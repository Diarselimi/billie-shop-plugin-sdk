<?php

declare(strict_types=1);

namespace App\Application\UseCase\CheckoutUpdateOrder;

use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Address\AddressEntity;
use App\DomainModel\Address\AddressRepositoryInterface;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorExternalData\DebtorExternalDataRepositoryInterface;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderRepositoryInterface;

class CheckoutUpdateOrderUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $orderContainerFactory;

    private $companiesService;

    private $addressRepository;

    private $debtorExternalDataRepository;

    private $orderRepository;

    public function __construct(
        OrderContainerFactory $orderContainerFactory,
        CompaniesServiceInterface $companiesService,
        AddressRepositoryInterface $addressRepository,
        DebtorExternalDataRepositoryInterface $debtorExternalDataRepository,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->orderContainerFactory = $orderContainerFactory;
        $this->companiesService = $companiesService;
        $this->addressRepository = $addressRepository;
        $this->debtorExternalDataRepository = $debtorExternalDataRepository;
        $this->orderRepository = $orderRepository;
    }

    public function execute(CheckoutUpdateOrderRequest $request): void
    {
        $this->validateRequest($request);

        try {
            $orderContainer = $this->orderContainerFactory->loadNotYetConfirmedByCheckoutSessionUuid(
                $request->getSessionUuid()
            );
        } catch (OrderContainerFactoryException $exception) {
            throw new OrderNotFoundException($exception);
        }

        $billingAddress = (new AddressEntity())
            ->setStreet($request->getBillingAddress()->getStreet())
            ->setHouseNumber($request->getBillingAddress()->getHouseNumber())
            ->setAddition($request->getBillingAddress()->getAddition())
            ->setPostalCode($request->getBillingAddress()->getPostalCode())
            ->setCity($request->getBillingAddress()->getCity())
            ->setCountry($request->getBillingAddress()->getCountry());

        $this->associateBillingAddressWithIdentifiedCompany($orderContainer, $billingAddress);
        $this->associateBillingAddressWithExternalData($orderContainer, $billingAddress);
    }

    private function associateBillingAddressWithIdentifiedCompany(
        OrderContainer $orderContainer,
        AddressEntity $billingAddress
    ) {
        $identificationBillingAddressUuid = $this->companiesService->updateCompanyBillingAddress(
            $orderContainer->getDebtorCompany()->getUuid(),
            $billingAddress
        );

        $this->orderRepository->updateIdentificationBillingAddress(
            $orderContainer->getOrder()->getId(),
            $identificationBillingAddressUuid->toString()
        );
    }

    private function associateBillingAddressWithExternalData(
        OrderContainer $orderContainer,
        AddressEntity $billingAddress
    ) {
        $this->addressRepository->insert($billingAddress);

        $externalData = $orderContainer->getDebtorExternalData();
        $externalData->setBillingAddressId($billingAddress->getId());
        $this->debtorExternalDataRepository->update($externalData);
    }
}
