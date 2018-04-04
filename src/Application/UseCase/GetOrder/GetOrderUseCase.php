<?php

namespace App\Application\UseCase\GetOrder;

use App\Application\PaellaCoreCriticalException;
use App\DomainModel\Alfred\AlfredInterface;
use App\DomainModel\Company\CompanyRepositoryInterface;
use App\DomainModel\Order\OrderRepositoryInterface;

class GetOrderUseCase
{
    private $orderRepository;
    private $companyRepository;
    private $alfred;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        CompanyRepositoryInterface $companyRepository,
        AlfredInterface $alfred
    ) {
        $this->orderRepository = $orderRepository;
        $this->companyRepository = $companyRepository;
        $this->alfred = $alfred;
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
            $response
                ->setCompanyName($debtor->getName())
                ->setCompanyAddressHouseNumber($debtor->getAddressHouse())
                ->setCompanyAddressStreet($debtor->getAddressStreet())
                ->setCompanyAddressPostalCode($debtor->getAddressPostalCode())
                ->setCompanyAddressCity($debtor->getAddressCity())
                ->setCompanyAddressCountry($debtor->getAddressCountry())
            ;
        }

        // call to borscht to get bank account
        // call to borscht to get order payment data

        return $response;
    }
}
