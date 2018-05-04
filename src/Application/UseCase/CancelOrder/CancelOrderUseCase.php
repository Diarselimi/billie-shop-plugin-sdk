<?php

namespace App\Application\UseCase\CancelOrder;

use App\Application\PaellaCoreCriticalException;
use App\DomainModel\Alfred\AlfredInterface;
use App\DomainModel\Borscht\BorschtInterface;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use App\Infrastructure\Repository\CompanyRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Workflow\Registry;

class CancelOrderUseCase
{
    private $orderRepository;
    private $alfred;
    private $borscht;
    private $workflows;
    private $companyRepository;

    public function __construct(
        Registry $workflows,
        OrderRepositoryInterface $orderRepository,
        AlfredInterface $alfred,
        BorschtInterface $borscht,
        CompanyRepository $companyRepository
    ) {
        $this->workflows = $workflows;
        $this->orderRepository = $orderRepository;
        $this->alfred = $alfred;
        $this->borscht = $borscht;
        $this->companyRepository = $companyRepository;
    }

    public function execute(CancelOrderRequest $request): void
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

        // Transition
        $stateMachine = $this->workflows->get($order);
        if ($stateMachine->can($order, OrderStateManager::TRANSITION_CANCEL)) {
            $stateMachine->apply($order, OrderStateManager::TRANSITION_CANCEL);
        } elseif ($stateMachine->can($order, OrderStateManager::TRANSITION_CANCEL_SHIPPED)) {
            $this->borscht->cancelOrder($order);
            $stateMachine->apply($order, OrderStateManager::TRANSITION_CANCEL_SHIPPED);
        } else {
            throw new PaellaCoreCriticalException(
                "Order #$externalCode can not be cancelled",
                PaellaCoreCriticalException::CODE_ORDER_CANT_BE_CANCELLED,
                Response::HTTP_BAD_REQUEST
            );
        }
        $this->orderRepository->updateState($order);

        // Unlock debtor limit
        $company = $this->companyRepository->getOneById($order->getCompanyId());
        if ($company === null) {
            throw new PaellaCoreCriticalException(sprintf('Company %s not found', $order->getCompanyId()));
        }
        $this->alfred->unlockDebtorLimit($company->getDebtorId(), $order->getAmountGross());
    }
}
