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

        if ($durationChanged && $this->orderStateManager->wasShipped($order) && !$this->orderStateManager->isLate($order)) {
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

        $order->setDuration($duration);
        $this->borscht->modifyOrder($order);
        $this->orderRepository->update($order);
    }

    private function updateAmount(OrderEntity $order, UpdateOrderRequest $request)
    {
        $this->logInfo('Update amount', [
            'old_gross' => $order->getAmountGross(),
            'new_gross' => $request->getAmountGross(),

            'old_net' => $order->getAmountNet(),
            'new_net' => $request->getAmountNet(),

            'old_tax' => $order->getAmountTax(),
            'new_tax' => $request->getAmountTax(),
        ]);

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

        if ($this->orderStateManager->wasShipped($order)) {
            $this->logInfo('Do partial cancellation in Borscht');
            $this->borscht->modifyOrder($order);
        }

        $merchantDebtor = $this->merchantDebtorRepository->getOneById($order->getMerchantDebtorId());
        $this->alfred->unlockDebtorLimit($merchantDebtor->getDebtorId(), $order->getAmountGross());

        $merchant = $this->merchantRepository->getOneById($order->getMerchantId());
        $merchant->increaseAvailableFinancingLimit($order->getAmountGross());
        $this->merchantRepository->update($merchant);

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
