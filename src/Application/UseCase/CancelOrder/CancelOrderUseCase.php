<?php

namespace App\Application\UseCase\CancelOrder;

use App\Application\PaellaCoreCriticalException;
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
    private $borscht;
    private $workflow;
    private $companyRepository;
    private $merchantRepository;

    public function __construct(
        Workflow $workflow,
        OrderRepositoryInterface $orderRepository,
        BorschtInterface $borscht,
        MerchantDebtorRepository $companyRepository,
        MerchantRepositoryInterface $merchantRepository
    ) {
        $this->workflow = $workflow;
        $this->orderRepository = $orderRepository;
        $this->borscht = $borscht;
        $this->companyRepository = $companyRepository;
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

        if ($this->workflow->can($order, OrderStateManager::TRANSITION_CANCEL)) {
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

        $company = $this->companyRepository->getOneById($order->getMerchantDebtorId());
        if ($company === null) {
            throw new PaellaCoreCriticalException(sprintf('Company %s not found', $order->getMerchantDebtorId()));
        }

        $merchant = $this->merchantRepository->getOneById($merchantId);
        $merchant->increaseAvailableFinancingLimit($order->getAmountGross());
        $this->merchantRepository->update($merchant);
    }
}
