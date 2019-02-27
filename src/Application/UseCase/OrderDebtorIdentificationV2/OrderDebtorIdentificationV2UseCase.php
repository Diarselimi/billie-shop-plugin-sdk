<?php

namespace App\Application\UseCase\OrderDebtorIdentificationV2;

use App\DomainModel\Address\AddressRepositoryInterface;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\IdentifyDebtorRequestDTO;
use App\DomainModel\DebtorExternalData\DebtorExternalDataRepositoryInterface;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderIdentification\OrderIdentificationEntity;
use App\DomainModel\OrderIdentification\OrderIdentificationRepositoryInterface;
use App\DomainModel\Person\PersonRepositoryInterface;
use App\Infrastructure\Alfred\AlfredRequestException;

class OrderDebtorIdentificationV2UseCase
{
    private $orderRepository;

    private $debtorExternalDataRepository;

    private $addressRepository;

    private $personRepository;

    private $orderIdentificationRepository;

    private $companiesService;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        DebtorExternalDataRepositoryInterface $debtorExternalDataRepository,
        AddressRepositoryInterface $addressRepository,
        PersonRepositoryInterface $personRepository,
        OrderIdentificationRepositoryInterface $orderIdentificationRepository,
        CompaniesServiceInterface $companiesService
    ) {
        $this->orderRepository = $orderRepository;
        $this->debtorExternalDataRepository = $debtorExternalDataRepository;
        $this->addressRepository = $addressRepository;
        $this->personRepository = $personRepository;
        $this->orderIdentificationRepository = $orderIdentificationRepository;
        $this->companiesService = $companiesService;
    }

    public function execute(OrderDebtorIdentificationV2Request $request): void
    {
        $order = $this->orderRepository->getOneById($request->getOrderId());

        if (!$order) {
            return;
        }

        $debtorExternalData = $this->debtorExternalDataRepository->getOneById($order->getDebtorExternalDataId());
        $debtorExternalAddress = $this->addressRepository->getOneById($debtorExternalData->getAddressId());
        $debtorPerson = $this->personRepository->getOneById($order->getDebtorPersonId());

        try {
            $identifiedDebtor = $this->companiesService->identifyDebtorV2(
                (new IdentifyDebtorRequestDTO())
                ->setName($debtorExternalData->getName())
                ->setHouseNumber($debtorExternalAddress->getHouseNumber())
                ->setStreet($debtorExternalAddress->getStreet())
                ->setPostalCode($debtorExternalAddress->getPostalCode())
                ->setCity($debtorExternalAddress->getCity())
                ->setCountry($debtorExternalAddress->getCountry())
                ->setTaxId($debtorExternalData->getTaxId())
                ->setTaxNumber($debtorExternalData->getTaxNumber())
                ->setRegistrationNumber($debtorExternalData->getRegistrationNumber())
                ->setRegistrationCourt($debtorExternalData->getRegistrationCourt())
                ->setLegalForm($debtorExternalData->getLegalForm())
                ->setFirstName($debtorPerson->getFirstName())
                ->setLastName($debtorPerson->getLastName())
            );

            $this->orderIdentificationRepository->insert(
                (new OrderIdentificationEntity())
                    ->setOrderId($request->getOrderId())
                    ->setV1CompanyId($request->getV1CompanyId())
                    ->setV2CompanyId($identifiedDebtor ? $identifiedDebtor->getId() : null)
            );
        } catch (AlfredRequestException $e) {
            $this->orderIdentificationRepository->insert(
                (new OrderIdentificationEntity())
                    ->setOrderId($request->getOrderId())
                    ->setV1CompanyId($request->getV1CompanyId())
                    ->setV2CompanyId(null)
            );
        }
    }
}
