<?php

namespace App\Application\UseCase\OrderDebtorIdentificationV2;

use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\IdentifyAndScoreDebtor\Exception\DebtorNotIdentifiedException;
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

    public function execute(OrderDebtorIdentificationV2Request $request): OrderDebtorIdentificationV2Response
    {
        try {
            if ($request->getOrderId()) {
                $orderContainer = $this->orderContainerFactory->loadById($request->getOrderId());
            } else {
                $orderContainer = $this->orderContainerFactory->loadByUuid($request->getOrderUuid());
            }
        } catch (OrderContainerFactoryException $exception) {
            throw new OrderNotFoundException($exception);
        }

        $debtorExternalData = $orderContainer->getDebtorExternalData();
        $debtorExternalAddress = $orderContainer->getDebtorExternalDataAddress();
        $debtorPerson = $orderContainer->getDebtorPerson();

        try {
            $identifyDebtorResponseDTO = $this->companiesService->identifyDebtor(
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
                ->setBillingAddress($orderContainer->getBillingAddress())
            );
            $identifiedDebtor = $identifyDebtorResponseDTO->getIdentifiedDebtorCompany();

            $this->orderIdentificationRepository->insert(
                (new OrderIdentificationEntity())
                    ->setOrderId($orderContainer->getOrder()->getId())
                    ->setV1CompanyId($request->getV1CompanyId())
                    ->setV2CompanyId($identifiedDebtor ? $identifiedDebtor->getId() : null)
                    ->setV2StrictMatch($identifiedDebtor ? $identifiedDebtor->isStrictMatch() : null)
            );

            if ($identifiedDebtor) {
                return new OrderDebtorIdentificationV2Response($identifiedDebtor);
            }
        } catch (CompaniesServiceRequestException $e) {
            $this->orderIdentificationRepository->insert(
                (new OrderIdentificationEntity())
                    ->setOrderId($request->getOrderId())
                    ->setV1CompanyId($request->getV1CompanyId())
                    ->setV2CompanyId(null)
            );
        }

        throw new DebtorNotIdentifiedException('Debtor not identified');
    }
}
