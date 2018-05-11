<?php

namespace App\Application\UseCase\UpdateOrder;

use App\Application\PaellaCoreCriticalException;
use App\DomainModel\Alfred\AlfredInterface;
use App\DomainModel\Borscht\BorschtInterface;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use App\Infrastructure\Repository\CompanyRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Workflow\Registry;

class UpdateOrderUseCase
{
    private $orderRepository;
    private $alfred;
    private $borscht;
    private $workflows;
    private $companyRepository;
    private $orderStateManager;

    public function __construct(
        Registry $workflows,
        OrderRepositoryInterface $orderRepository,
        AlfredInterface $alfred,
        BorschtInterface $borscht,
        CompanyRepository $companyRepository,
        OrderStateManager $orderStateManager
    ) {
        $this->workflows = $workflows;
        $this->orderRepository = $orderRepository;
        $this->alfred = $alfred;
        $this->borscht = $borscht;
        $this->companyRepository = $companyRepository;
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

        // Amount
        if ($request->getAmountGross() !== null && (float)$request->getAmountGross() !== $order->getAmountGross()) {
            $amountChanged = $order->getAmountGross() - $request->getAmountGross();
            $order
                ->setAmountGross($request->getAmountGross())
                ->setAmountNet($request->getAmountNet())
                ->setAmountTax($request->getAmountTax());
            $changed = true;
        }

        // Duration
        if ($request->getDuration() !== null && $request->getDuration() !== $order->getDuration()) {
            if ($this->orderStateManager->wasShipped($order)) {
                $order
                    ->setDuration($request->getDuration());
                $changed = true;
            }
        }

        if ($changed) {
            // Modify order in borscht
            if ($this->orderStateManager->wasShipped($order)) {
                $this->borscht->modifyOrder($order);
            }

            // Unlock debtor limit in alfred
            if ($amountChanged !== 0) {
                $company = $this->companyRepository->getOneById($order->getCompanyId());
                if ($company === null) {
                    throw new PaellaCoreCriticalException(sprintf('Company %s not found', $order->getCompanyId()));
                }
                $this->alfred->unlockDebtorLimit($company->getDebtorId(), $amountChanged);
            }

            // Update the order
            $this->orderRepository->update($order);
        }
    }

    private function validate(OrderEntity $order, UpdateOrderRequest $request): void
    {
        if (!empty($request->getAmountGross()) &&
            ($request->getAmountGross() > $order->getAmountGross() || $request->getAmountNet() > $order->getAmountNet() || $request->getAmountTax() > $order->getAmountTax())
        ) {
            throw new PaellaCoreCriticalException(
                'Invalid amount',
                PaellaCoreCriticalException::CODE_ORDER_VALIDATION_FAILED
            );
        }
        if (!empty($request->getDuration()) && $request->getDuration() < $order->getDuration()) {
            throw new PaellaCoreCriticalException(
                'Invalid duration',
                PaellaCoreCriticalException::CODE_ORDER_VALIDATION_FAILED
            );
        }
    }
}
