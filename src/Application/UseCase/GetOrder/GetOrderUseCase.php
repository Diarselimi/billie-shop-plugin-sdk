<?php

namespace App\Application\UseCase\GetOrder;

use App\Application\PaellaCoreCriticalException;
use App\DomainModel\Alfred\AlfredInterface;
use App\DomainModel\Borscht\BorschtInterface;
use App\DomainModel\Company\CompanyRepositoryInterface;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;

class GetOrderUseCase
{
    private $orderRepository;
    private $companyRepository;
    private $alfred;
    private $borscht;
    private $orderStateManager;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        CompanyRepositoryInterface $companyRepository,
        AlfredInterface $alfred,
        BorschtInterface $borscht,
        OrderStateManager $orderStateManager
    ) {
        $this->orderRepository = $orderRepository;
        $this->companyRepository = $companyRepository;
        $this->alfred = $alfred;
        $this->borscht = $borscht;
        $this->orderStateManager = $orderStateManager;
    }

    public function execute(GetOrderRequest $request): GetOrderResponse
    {
        $externalCode = $request->getExternalCode();
        $customerId = $request->getCustomerId();
        $order = $this->orderRepository->getOneByExternalCode($externalCode, $customerId);

        if (!$order) {
            throw new PaellaCoreCriticalException(
                "Order #$externalCode not found",
                PaellaCoreCriticalException::CODE_NOT_FOUND
            );
        }

        $response = (new GetOrderResponse())
            ->setExternalCode($order->getExternalCode())
            ->setState($order->getState())
        ;

        if ($order->getCompanyId()) {
            $company = $this->companyRepository->getOneById($order->getCompanyId());
            $debtor = $this->alfred->getDebtor($company->getDebtorId());
            $debtorPaymentDetails = $this->borscht->getDebtorPaymentDetails($debtor->getPaymentId());

            $response
                ->setCompanyName($debtor->getName())
                ->setCompanyAddressHouseNumber($debtor->getAddressHouse())
                ->setCompanyAddressStreet($debtor->getAddressStreet())
                ->setCompanyAddressPostalCode($debtor->getAddressPostalCode())
                ->setCompanyAddressCity($debtor->getAddressCity())
                ->setCompanyAddressCountry($debtor->getAddressCountry())
                ->setBankAccountIban($debtorPaymentDetails->getBankAccountIban())
                ->setBankAccountBic($debtorPaymentDetails->getBankAccountBic())
            ;
        }

        if ($this->orderStateManager->wasShipped($order)) {
            $orderPaymentDetails = $this->borscht->getOrderPaymentDetails($order->getPaymentId());
            $response
                ->setInvoiceNumber($order->getInvoiceNumber())
                ->setPayoutAmount($orderPaymentDetails->getPayoutAmount())
                ->setFeeRate($orderPaymentDetails->getFeeRate())
                ->setFeeAmount($orderPaymentDetails->getFeeAmount())
                ->setDueDate($orderPaymentDetails->getDueDate())
            ;
        }

        return $response;
    }
}
