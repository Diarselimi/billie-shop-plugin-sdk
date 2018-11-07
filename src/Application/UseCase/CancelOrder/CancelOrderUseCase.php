<?php

namespace App\Application\UseCase\CancelOrder;

use App\Application\Exception\FraudOrderException;
use App\Application\PaellaCoreCriticalException;
use App\DomainModel\Alfred\AlfredInterface;
use App\DomainModel\Borscht\BorschtInterface;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use App\Infrastructure\Repository\MerchantDebtorRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Workflow\Workflow;

class CancelOrderUseCase
{
    private $orderRepository;

    private $alfred;

    private $borscht;

    private $workflow;

    private $merchantDebtorRepository;

    private $merchantRepository;

    public function __construct(
        Workflow $workflow,
        OrderRepositoryInterface $orderRepository,
        AlfredInterface $alfred,
        BorschtInterface $borscht,
        MerchantDebtorRepository $merchantDebtorRepository,
        MerchantRepositoryInterface $merchantRepository
    ) {
        $this->workflow = $workflow;
        $this->orderRepository = $orderRepository;
        $this->alfred = $alfred;
        $this->borscht = $borscht;
        $this->merchantDebtorRepository = $merchantDebtorRepository;
        $this->merchantRepository = $merchantRepository;
    }

    public function execute(CancelOrderRequest $request): void
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

        if ($order->getMarkedAsFraudAt()) {
            throw new FraudOrderException();
        }

        if ($this->workflow->can($order, OrderStateManager::TRANSITION_CANCEL)) {
            $merchant = $this->merchantRepository->getOneById($merchantId);
            $merchant->increaseAvailableFinancingLimit($order->getAmountGross());
            $this->merchantRepository->update($merchant);

            $company = $this->merchantDebtorRepository->getOneById($order->getMerchantDebtorId());
            $this->alfred->unlockDebtorLimit($company->getDebtorId(), $order->getAmountGross());

            $this->workflow->apply($order, OrderStateManager::TRANSITION_CANCEL);
        } elseif ($this->workflow->can($order, OrderStateManager::TRANSITION_CANCEL_SHIPPED)) {
            $this->borscht->cancelOrder($order);
            $this->workflow->apply($order, OrderStateManager::TRANSITION_CANCEL_SHIPPED);
        } else {
            throw new PaellaCoreCriticalException(
                "Order #$externalCode can not be cancelled",
                PaellaCoreCriticalException::CODE_ORDER_CANT_BE_CANCELLED
            );
        }
        $this->orderRepository->update($order);

        $company = $this->merchantDebtorRepository->getOneById($order->getMerchantDebtorId());
        if ($company === null) {
            throw new PaellaCoreCriticalException(sprintf('Company %s not found', $order->getMerchantDebtorId()));
        }
    }
}
