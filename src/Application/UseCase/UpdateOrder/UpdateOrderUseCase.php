<?php

namespace App\Application\UseCase\UpdateOrder;

use App\Application\Exception\FraudOrderException;
use App\Application\PaellaCoreCriticalException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Borscht\BorschtInterface;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\Order\LimitsService;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\HttpFoundation\Response;

class UpdateOrderUseCase implements LoggingInterface, ValidatedUseCaseInterface
{
    use LoggingTrait, ValidatedUseCaseTrait;

    private $borscht;

    private $limitsService;

    private $orderRepository;

    private $merchantDebtorRepository;

    private $merchantRepository;

    private $orderStateManager;

    public function __construct(
        BorschtInterface $borscht,
        LimitsService $limitsService,
        OrderRepositoryInterface $orderRepository,
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        MerchantRepositoryInterface $merchantRepository,
        OrderStateManager $orderStateManager
    ) {
        $this->borscht = $borscht;
        $this->limitsService = $limitsService;
        $this->orderRepository = $orderRepository;
        $this->merchantDebtorRepository = $merchantDebtorRepository;
        $this->merchantRepository = $merchantRepository;
        $this->orderStateManager = $orderStateManager;
    }

    public function execute(UpdateOrderRequest $request): void
    {
        $this->validateRequest($request);

        $externalCode = $request->getExternalCode();
        $merchantId = $request->getMerchantId();
        $order = $this->orderRepository->getOneByExternalCode($externalCode, $merchantId);

        if (!$order) {
            throw new PaellaCoreCriticalException(
                "Order #$externalCode not found",
                PaellaCoreCriticalException::CODE_NOT_FOUND,
                Response::HTTP_NOT_FOUND
            );
        }

        if ($order->getMarkedAsFraudAt()) {
            throw new FraudOrderException();
        }

        $this->validate($order, $request);
        $this->updateChangedData($order, $request);
    }

    private function updateChangedData(OrderEntity $order, UpdateOrderRequest $request)
    {
        $durationChanged = $request->getDuration() !== null && $request->getDuration() !== $order->getDuration();
        $amountChanged = $request->getAmountGross() !== null && (float) $request->getAmountGross() !== $order->getAmountGross()
            || $request->getAmountNet() !== null && (float) $request->getAmountNet() !== $order->getAmountNet()
            || $request->getAmountTax() !== null && (float) $request->getAmountTax() !== $order->getAmountTax();
        $invoiceChanged = !$amountChanged
            && $request->getInvoiceNumber() !== null && $request->getInvoiceNumber() !== $order->getInvoiceNumber()
            || $request->getInvoiceUrl() !== null && $request->getInvoiceUrl() !== $order->getInvoiceUrl();

        $this->logInfo('Start order update, state {state}, duration changed: {duration}, amount changed: {amount}', [
            'state' => $order->getState(),
            'duration_changed' => (int) $durationChanged,
            'amount_changed' => (int) $amountChanged,
        ]);

        if ($amountChanged && ($this->orderStateManager->wasShipped($order) || !$durationChanged)) {
            $this->updateAmount($order, $request);
        }

        if ($invoiceChanged) {
            $this->updateInvoiceDetails($order, $request);
        }

        if ($durationChanged) {
            $this->updateDuration($order, $request);
        }
    }

    private function updateDuration(OrderEntity $order, UpdateOrderRequest $request)
    {
        $duration = $request->getDuration();

        $this->logInfo('Update duration', [
            'old' => $order->getDuration(),
            'new' => $duration,
        ]);

        if (!$this->orderStateManager->wasShipped($order) || $this->orderStateManager->isLate($order)) {
            throw new PaellaCoreCriticalException(
                'Update duration not possible',
                PaellaCoreCriticalException::CODE_ORDER_DURATION_CANT_BE_UPDATED,
                Response::HTTP_PRECONDITION_FAILED
            );
        }

        $order->setDuration($duration);
        $this->applyUpdate($order);
    }

    private function updateAmount(OrderEntity $order, UpdateOrderRequest $request)
    {
        if ($this->orderStateManager->isCanceled($order) || $this->orderStateManager->isComplete($order)) {
            throw new PaellaCoreCriticalException(
                'Update amount not possible',
                PaellaCoreCriticalException::CODE_ORDER_AMOUNT_CANT_BE_UPDATED,
                Response::HTTP_PRECONDITION_FAILED
            );
        }

        $this->logInfo('Update amount', [
            'old_gross' => $order->getAmountGross(),
            'new_gross' => $request->getAmountGross(),

            'old_net' => $order->getAmountNet(),
            'new_net' => $request->getAmountNet(),

            'old_tax' => $order->getAmountTax(),
            'new_tax' => $request->getAmountTax(),
        ]);

        $amountChanged = $order->getAmountGross() - $request->getAmountGross();
        $order
            ->setAmountGross($request->getAmountGross())
            ->setAmountNet($request->getAmountNet())
            ->setAmountTax($request->getAmountTax());

        if ($this->orderStateManager->wasShipped($order)) {
            $order
                ->setInvoiceNumber($request->getInvoiceNumber())
                ->setInvoiceUrl($request->getInvoiceUrl());
        }

        if ($amountChanged == 0.0) {
            $this->logInfo('Gross amount was not changed, do nothing');

            return;
        }

        if ($this->orderStateManager->wasShipped($order)) {
            $this->logInfo('Do partial cancellation in Borscht');
            $this->applyUpdate($order);

            return;
        }

        $this->logInfo('Do update order without Borscht');
        $this->orderRepository->update($order);

        $this->logInfo('Do update merchant limit without Borscht');
        $this->updateMerchantLimit($order, $amountChanged);
    }

    private function updateMerchantLimit(OrderEntity $order, float $amountChanged)
    {
        $merchantDebtor = $this->merchantDebtorRepository->getOneById($order->getMerchantDebtorId());
        $this->limitsService->unlock($merchantDebtor, $amountChanged);

        $merchant = $this->merchantRepository->getOneById($order->getMerchantId());
        $merchant->increaseAvailableFinancingLimit($amountChanged);
        $this->merchantRepository->update($merchant);
    }

    private function updateInvoiceDetails(OrderEntity $order, UpdateOrderRequest $request): void
    {
        if ($this->orderStateManager->isCanceled($order) || $this->orderStateManager->isComplete($order)
            || !$this->orderStateManager->wasShipped($order)
        ) {
            throw new PaellaCoreCriticalException(
                'Update invoice is not possible',
                PaellaCoreCriticalException::CODE_ORDER_INVOICE_CANT_BE_UPDATED,
                Response::HTTP_PRECONDITION_FAILED
            );
        }

        $order->setInvoiceNumber($request->getInvoiceNumber())->setInvoiceUrl($request->getInvoiceUrl());

        $this->applyUpdate($order);
    }

    private function applyUpdate(OrderEntity $order)
    {
        try {
            $this->borscht->modifyOrder($order);
        } catch (PaellaCoreCriticalException $e) {
            $this->logError(
                'Borscht responded with an error when updating the order.',
                [
                    'order' => $order->getId(),
                    'error' => $e,
                ]
            );

            throw $e;
        }
        $this->orderRepository->update($order);
    }

    private function validate(OrderEntity $order, UpdateOrderRequest $request): void
    {
        //TODO: does not belong here
        if (!empty($request->getAmountGross()) && (
                $request->getAmountGross() > $order->getAmountGross()
                || $request->getAmountNet() > $order->getAmountNet()
                || $request->getAmountTax() > $order->getAmountTax()
            )) {
            throw new PaellaCoreCriticalException(
                'Invalid amount',
                PaellaCoreCriticalException::CODE_ORDER_VALIDATION_FAILED,
                Response::HTTP_PRECONDITION_FAILED
            );
        }

        if (!empty($request->getDuration()) && $request->getDuration() < $order->getDuration()) {
            throw new PaellaCoreCriticalException(
                'Invalid duration',
                PaellaCoreCriticalException::CODE_ORDER_VALIDATION_FAILED,
                Response::HTTP_PRECONDITION_FAILED
            );
        }
    }
}
