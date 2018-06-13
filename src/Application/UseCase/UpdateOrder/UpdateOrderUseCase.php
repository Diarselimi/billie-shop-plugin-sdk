<?php

namespace App\Application\UseCase\UpdateOrder;

use App\Application\PaellaCoreCriticalException;
use App\DomainModel\Alfred\AlfredInterface;
use App\DomainModel\Borscht\BorschtInterface;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use Symfony\Component\HttpFoundation\Response;

class UpdateOrderUseCase
{
    private $orderRepository;
    private $alfred;
    private $borscht;
    private $merchantDebtorRepository;
    private $orderStateManager;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        AlfredInterface $alfred,
        BorschtInterface $borscht,
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        OrderStateManager $orderStateManager
    ) {
        $this->orderRepository = $orderRepository;
        $this->alfred = $alfred;
        $this->borscht = $borscht;
        $this->merchantDebtorRepository = $merchantDebtorRepository;
        $this->orderStateManager = $orderStateManager;
    }

    public function execute(UpdateOrderRequest $request): void
    {
        $externalCode = $request->getExternalCode();
        $customerId = $request->getCustomerId();
        $order = $this->orderRepository->getOneByExternalCode($externalCode, $customerId);

        if (!$order) {
            throw new PaellaCoreCriticalException(
                "Order #$externalCode not found",
                PaellaCoreCriticalException::CODE_NOT_FOUND,
                Response::HTTP_NOT_FOUND
            );
        }

        $this->validate($order, $request);

        $changed = false;
        $amountChanged = 0;
        $durationChanged = false;

        // Duration
        if ($request->getDuration() !== null && $request->getDuration() !== $order->getDuration()) {
            if ($this->orderStateManager->wasShipped($order)) {
                $order->setDuration($request->getDuration());
                $changed = true;
            }
            $durationChanged = true;
        }

        // Amount
        if ($request->getAmountGross() !== null && (float)$request->getAmountGross() !== $order->getAmountGross()
            || $request->getAmountNet() !== null && (float)$request->getAmountNet() !== $order->getAmountNet()
            || $request->getAmountTax() !== null && (float)$request->getAmountTax() !== $order->getAmountTax()
        ) {
            if ($this->orderStateManager->wasShipped($order) || !$durationChanged) {
                $amountChanged = $order->getAmountGross() - $request->getAmountGross();
                $order
                    ->setAmountGross($request->getAmountGross())
                    ->setAmountNet($request->getAmountNet())
                    ->setAmountTax($request->getAmountTax());
                $changed = true;
            }
        }

        if ($changed) {
            if ($amountChanged !== 0) {
                $order
                    ->setInvoiceNumber($request->getInvoiceNumber())
                    ->setInvoiceUrl($request->getInvoiceUrl())
                ;
            }

            // Modify order in borscht
            if ($this->orderStateManager->wasShipped($order)) {
                $this->borscht->modifyOrder($order);
            }

            // Unlock debtor limit in alfred
            if ($amountChanged !== 0) {
                $merchantDebtor = $this->merchantDebtorRepository->getOneById($order->getMerchantDebtorId());
                if ($merchantDebtor === null) {
                    throw new PaellaCoreCriticalException(sprintf(
                        'Company %s not found',
                        $order->getMerchantDebtorId()
                    ));
                }
                $this->alfred->unlockDebtorLimit($merchantDebtor->getDebtorId(), $amountChanged);
            }

            // Update the order
            $this->orderRepository->update($order);
        }
    }

    private function validate(OrderEntity $order, UpdateOrderRequest $request): void
    {
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
