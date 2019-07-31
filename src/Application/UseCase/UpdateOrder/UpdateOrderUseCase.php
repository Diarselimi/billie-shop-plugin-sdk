<?php

namespace App\Application\UseCase\UpdateOrder;

use App\Application\Exception\FraudOrderException;
use App\Application\Exception\OrderNotFoundException;
use App\Application\PaellaCoreCriticalException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Payment\PaymentsServiceInterface;
use App\DomainModel\Payment\PaymentsServiceRequestException;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantDebtor\Limits\MerchantDebtorLimitsService;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsFactory;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsRepositoryInterface;
use App\DomainModel\OrderInvoice\InvoiceUploadHandlerInterface;
use App\DomainModel\OrderInvoice\OrderInvoiceManager;
use App\DomainModel\OrderInvoice\OrderInvoiceUploadException;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\HttpFoundation\Response;

class UpdateOrderUseCase implements LoggingInterface, ValidatedUseCaseInterface
{
    use LoggingTrait, ValidatedUseCaseTrait;

    private $orderContainerFactory;

    private $paymentsService;

    private $limitsService;

    private $orderRepository;

    private $merchantRepository;

    private $orderStateManager;

    private $invoiceManager;

    private $orderFinancialDetailsFactory;

    private $orderFinancialDetailsRepository;

    public function __construct(
        OrderContainerFactory $orderContainerFactory,
        PaymentsServiceInterface $paymentsService,
        MerchantDebtorLimitsService $limitsService,
        OrderRepositoryInterface $orderRepository,
        MerchantRepositoryInterface $merchantRepository,
        OrderStateManager $orderStateManager,
        OrderInvoiceManager $invoiceManager,
        OrderFinancialDetailsFactory $orderFinancialDetailsFactory,
        OrderFinancialDetailsRepositoryInterface $orderFinancialDetailsRepository
    ) {
        $this->orderContainerFactory = $orderContainerFactory;
        $this->paymentsService = $paymentsService;
        $this->limitsService = $limitsService;
        $this->orderRepository = $orderRepository;
        $this->merchantRepository = $merchantRepository;
        $this->orderStateManager = $orderStateManager;
        $this->invoiceManager = $invoiceManager;
        $this->orderFinancialDetailsFactory = $orderFinancialDetailsFactory;
        $this->orderFinancialDetailsRepository = $orderFinancialDetailsRepository;
    }

    public function execute(UpdateOrderRequest $request): void
    {
        $this->validateRequest($request);

        try {
            $orderContainer = $this->orderContainerFactory->loadByMerchantIdAndExternalId(
                $request->getMerchantId(),
                $request->getOrderId()
            );
        } catch (OrderContainerFactoryException $exception) {
            throw new OrderNotFoundException($exception);
        }

        if ($orderContainer->getOrder()->getMarkedAsFraudAt()) {
            throw new FraudOrderException();
        }

        $this->validate($orderContainer, $request);
        $this->updateChangedData($orderContainer, $request);
    }

    private function updateChangedData(OrderContainer $orderContainer, UpdateOrderRequest $request)
    {
        $order = $orderContainer->getOrder();
        $orderFinancialDetails = $orderContainer->getOrderFinancialDetails();

        $durationChanged = $request->getDuration() !== null && $request->getDuration() !== $orderFinancialDetails->getDuration();

        $amountChanged = $request->getAmount()->getGross() !== null
            && (float) $request->getAmount()->getGross() !== $orderFinancialDetails->getAmountGross()
            || $request->getAmount()->getNet() !== null && (float) $request->getAmount()->getNet() !== $orderFinancialDetails->getAmountNet()
            || $request->getAmount()->getTax() !== null && (float) $request->getAmount()->getTax() !== $orderFinancialDetails->getAmountTax();

        $invoiceChanged = (
            $request->getInvoiceNumber() && $request->getInvoiceNumber() !== $order->getInvoiceNumber()
            || $request->getInvoiceUrl() && $request->getInvoiceUrl() !== $order->getInvoiceUrl()
        );

        $this->logInfo('Start order update, state {state}, duration changed: {duration}, amount changed: {amount}', [
            'state' => $order->getState(),
            'duration_changed' => (int) $durationChanged,
            'amount_changed' => (int) $amountChanged,
        ]);

        if ($amountChanged && ($this->orderStateManager->wasShipped($order) || !$durationChanged)) {
            $this->updateAmount($orderContainer, $request);
        }

        if ($invoiceChanged) {
            $this->updateInvoiceDetails($orderContainer, $request);
        }

        if ($durationChanged) {
            $this->updateDuration($orderContainer, $request);
        }

        if ($amountChanged || $invoiceChanged || $durationChanged) {
            $this->applyUpdate($orderContainer);
        }
    }

    private function updateDuration(OrderContainer $orderContainer, UpdateOrderRequest $request)
    {
        $order = $orderContainer->getOrder();

        $newDuration = $request->getDuration();

        $this->logInfo('Update duration', [
            'old' => $orderContainer->getOrderFinancialDetails()->getDuration(),
            'new' => $newDuration,
        ]);

        if (!$this->orderStateManager->wasShipped($order) || $this->orderStateManager->isLate($order)) {
            throw new PaellaCoreCriticalException(
                'Update duration not possible',
                PaellaCoreCriticalException::CODE_ORDER_DURATION_CANT_BE_UPDATED,
                Response::HTTP_PRECONDITION_FAILED
            );
        }

        $newOrderFinancialDetails = $this->orderFinancialDetailsFactory->create(
            $order->getId(),
            $orderContainer->getOrderFinancialDetails()->getAmountGross(),
            $orderContainer->getOrderFinancialDetails()->getAmountNet(),
            $orderContainer->getOrderFinancialDetails()->getAmountTax(),
            $newDuration
        );
        $this->orderFinancialDetailsRepository->insert($newOrderFinancialDetails);

        $orderContainer->setOrderFinancialDetails($newOrderFinancialDetails);
    }

    private function updateAmount(OrderContainer $orderContainer, UpdateOrderRequest $request)
    {
        $order = $orderContainer->getOrder();
        $amountChanged = $orderContainer->getOrderFinancialDetails()->getAmountGross() - $request->getAmount()->getGross();

        if ($this->orderStateManager->isCanceled($order) || $this->orderStateManager->isComplete($order)) {
            throw new PaellaCoreCriticalException(
                'Update amount not possible',
                PaellaCoreCriticalException::CODE_ORDER_AMOUNT_CANT_BE_UPDATED,
                Response::HTTP_PRECONDITION_FAILED
            );
        }

        $this->logInfo('Update amount', [
            'old_gross' => $orderContainer->getOrderFinancialDetails()->getAmountGross(),
            'new_gross' => $request->getAmount()->getGross(),

            'old_net' => $orderContainer->getOrderFinancialDetails()->getAmountNet(),
            'new_net' => $request->getAmount()->getNet(),

            'old_tax' => $orderContainer->getOrderFinancialDetails()->getAmountTax(),
            'new_tax' => $request->getAmount()->getTax(),
        ]);

        $newOrderFinancialDetails = $this->orderFinancialDetailsFactory->create(
            $order->getId(),
            $request->getAmount()->getGross(),
            $request->getAmount()->getNet(),
            $request->getAmount()->getTax(),
            $orderContainer->getOrderFinancialDetails()->getDuration()
        );
        $this->orderFinancialDetailsRepository->insert($newOrderFinancialDetails);

        $orderContainer->setOrderFinancialDetails($newOrderFinancialDetails);

        if ($this->orderStateManager->wasShipped($order)) {
            return;
        }

        $this->updateLimits($orderContainer, $amountChanged);
    }

    private function updateLimits(OrderContainer $orderContainer, float $amountChanged)
    {
        $this->limitsService->unlock($orderContainer, $amountChanged);

        $orderContainer->getMerchant()->increaseFinancingLimit($amountChanged);
        $this->merchantRepository->update($orderContainer->getMerchant());
    }

    private function updateInvoiceDetails(OrderContainer $orderContainer, UpdateOrderRequest $request): void
    {
        $order = $orderContainer->getOrder();

        if (!$this->orderStateManager->wasShipped($order)) {
            throw new PaellaCoreCriticalException(
                'Update invoice is not possible',
                PaellaCoreCriticalException::CODE_ORDER_INVOICE_CANT_BE_UPDATED,
                Response::HTTP_PRECONDITION_FAILED
            );
        }

        $orderContainer->getOrder()
            ->setInvoiceNumber($request->getInvoiceNumber())
            ->setInvoiceUrl($request->getInvoiceUrl())
        ;

        try {
            $this->invoiceManager->upload($order, InvoiceUploadHandlerInterface::EVENT_UPDATE);
        } catch (OrderInvoiceUploadException $exception) {
            throw new PaellaCoreCriticalException(
                "Update invoice is not possible",
                PaellaCoreCriticalException::CODE_ORDER_INVOICE_CANT_BE_UPDATED,
                500,
                $exception
            );
        }
    }

    private function applyUpdate(OrderContainer $orderContainer)
    {
        $order = $orderContainer->getOrder();
        $this->orderRepository->update($order);

        if (!$this->orderStateManager->wasShipped($order)) {
            return;
        }

        try {
            $this->paymentsService->modifyOrder(
                $order->getPaymentId(),
                $orderContainer->getOrderFinancialDetails()->getDuration(),
                $orderContainer->getOrderFinancialDetails()->getAmountGross(),
                $order->getInvoiceNumber()
            );
        } catch (PaymentsServiceRequestException $exception) {
            $this->logError('Payment responded with an error when updating the order', [
                'order' => $order->getId(),
                'error' => $exception,
            ]);

            throw $exception;
        }
    }

    private function validate(OrderContainer $orderContainer, UpdateOrderRequest $request): void
    {
        //TODO: does not belong here (homeless validation logic)

        $orderFinancialDetails = $orderContainer->getOrderFinancialDetails();

        if (!empty($request->getAmount()->getGross()) && (
            $request->getAmount()->getGross() > $orderFinancialDetails->getAmountGross()
                || $request->getAmount()->getNet() > $orderFinancialDetails->getAmountNet()
                || $request->getAmount()->getTax() > $orderFinancialDetails->getAmountTax()
            )) {
            throw new PaellaCoreCriticalException(
                'Invalid amount',
                PaellaCoreCriticalException::CODE_ORDER_VALIDATION_FAILED,
                Response::HTTP_PRECONDITION_FAILED
            );
        }

        if (!empty($request->getDuration()) && $request->getDuration() < $orderFinancialDetails->getDuration()) {
            throw new PaellaCoreCriticalException(
                'Invalid duration',
                PaellaCoreCriticalException::CODE_ORDER_VALIDATION_FAILED,
                Response::HTTP_PRECONDITION_FAILED
            );
        }
    }
}
