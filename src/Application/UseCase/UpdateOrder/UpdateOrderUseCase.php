<?php

namespace App\Application\UseCase\UpdateOrder;

use App\Application\PaellaCoreCriticalException;
use App\DomainModel\Alfred\AlfredInterface;
use App\DomainModel\Borscht\BorschtInterface;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\Monitoring\LoggingInterface;
use App\DomainModel\Monitoring\LoggingTrait;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use Symfony\Component\HttpFoundation\Response;

class UpdateOrderUseCase implements LoggingInterface
{
    use LoggingTrait;

    private $borscht;
    private $alfred;
    private $orderRepository;
    private $merchantDebtorRepository;
    private $merchantRepository;
    private $orderStateManager;

    public function __construct(
        BorschtInterface $borscht,
        AlfredInterface $alfred,
        OrderRepositoryInterface $orderRepository,
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        MerchantRepositoryInterface $merchantRepository,
        OrderStateManager $orderStateManager
    ) {
        $this->borscht = $borscht;
        $this->alfred = $alfred;
        $this->orderRepository = $orderRepository;
        $this->merchantDebtorRepository = $merchantDebtorRepository;
        $this->merchantRepository = $merchantRepository;
        $this->orderStateManager = $orderStateManager;
    }

    public function execute(UpdateOrderRequest $request): void
    {
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

        $this->validate($order, $request);

        $durationChanged = $request->getDuration() !== null && $request->getDuration() !== $order->getDuration();
        $amountChanged = $request->getAmountGross() !== null && (float)$request->getAmountGross() !== $order->getAmountGross()
            || $request->getAmountNet() !== null && (float)$request->getAmountNet() !== $order->getAmountNet()
            || $request->getAmountTax() !== null && (float)$request->getAmountTax() !== $order->getAmountTax()
        ;

        $this->logInfo('Start order update, state {state}, duration changed: {duration}, amount changed: {amount}', [
            'state' => $order->getState(),
            'duration' => (int) $durationChanged,
            'amount' => (int) $amountChanged,
        ]);

        if ($amountChanged && ($this->orderStateManager->wasShipped($order) || !$durationChanged)) {
            $this->updateAmount($order, $request);
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
        $this->borscht->modifyOrder($order);
        $this->orderRepository->update($order);
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
            ->setAmountTax($request->getAmountTax())
        ;

        if ($this->orderStateManager->wasShipped($order)) {
            $order
                ->setInvoiceNumber($request->getInvoiceNumber())
                ->setInvoiceUrl($request->getInvoiceUrl())
            ;
        }

        $this->orderRepository->update($order);

        if ($amountChanged == 0.) {
            $this->logInfo('Gross amount was not changed, do nothing');

            return;
        }

        if ($this->orderStateManager->wasShipped($order)) {
            $this->logInfo('Do partial cancellation in Borscht');
            $this->borscht->modifyOrder($order);

            return;
        }

        $this->logInfo('Do update order without Borscht');

        $merchantDebtor = $this->merchantDebtorRepository->getOneById($order->getMerchantDebtorId());
        $this->alfred->unlockDebtorLimit($merchantDebtor->getDebtorId(), $amountChanged);

        $merchant = $this->merchantRepository->getOneById($order->getMerchantId());
        $merchant->increaseAvailableFinancingLimit($amountChanged);
        $this->merchantRepository->update($merchant);
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
