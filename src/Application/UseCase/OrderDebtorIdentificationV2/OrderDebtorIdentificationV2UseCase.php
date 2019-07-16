<?php

namespace App\Application\UseCase\OrderDebtorIdentificationV2;

use App\Application\Exception\OrderNotFoundException;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\CompaniesServiceRequestException;
use App\DomainModel\DebtorCompany\IdentifyDebtorRequestDTO;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\OrderIdentification\OrderIdentificationEntity;
use App\DomainModel\OrderIdentification\OrderIdentificationRepositoryInterface;

class OrderDebtorIdentificationV2UseCase
{
    private $orderContainerFactory;

    private $orderIdentificationRepository;

    private $companiesService;

    public function __construct(
        OrderContainerFactory $orderContainerFactory,
        OrderIdentificationRepositoryInterface $orderIdentificationRepository,
        CompaniesServiceInterface $companiesService
    ) {
        $this->orderContainerFactory = $orderContainerFactory;
        $this->orderIdentificationRepository = $orderIdentificationRepository;
        $this->companiesService = $companiesService;
    }

    public function execute(OrderDebtorIdentificationV2Request $request): void
    {
        try {
            $orderContainer = $this->orderContainerFactory->loadById($request->getOrderId());
        } catch (OrderContainerFactoryException $exception) {
            throw new OrderNotFoundException($exception);
        }

        $debtorExternalData = $orderContainer->getDebtorExternalData();
        $debtorExternalAddress = $orderContainer->getDebtorExternalDataAddress();
        $debtorPerson = $orderContainer->getDebtorPerson();

        try {
            $identifiedDebtor = $this->companiesService->identifyDebtor(
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
                ->setIsExperimental(true)
            );

            $this->orderIdentificationRepository->insert(
                (new OrderIdentificationEntity())
                    ->setOrderId($request->getOrderId())
                    ->setV1CompanyId($request->getV1CompanyId())
                    ->setV2CompanyId($identifiedDebtor ? $identifiedDebtor->getId() : null)
                    ->setV2StrictMatch($identifiedDebtor ? $identifiedDebtor->isStrictMatch() : null)
            );
        } catch (CompaniesServiceRequestException $e) {
            $this->orderIdentificationRepository->insert(
                (new OrderIdentificationEntity())
                    ->setOrderId($request->getOrderId())
                    ->setV1CompanyId($request->getV1CompanyId())
                    ->setV2CompanyId(null)
            );
        }
    }
}
