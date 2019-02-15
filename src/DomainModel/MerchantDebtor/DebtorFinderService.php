<?php

namespace App\DomainModel\MerchantDebtor;

use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\IdentifyDebtorRequestDTO;
use App\DomainModel\Monitoring\LoggingInterface;
use App\DomainModel\Monitoring\LoggingTrait;
use App\DomainModel\Order\OrderContainer;

class DebtorFinderService implements LoggingInterface
{
    use LoggingTrait;

    private $merchantDebtorRepository;

    private $companiesService;

    private $merchantDebtorRegistrationService;

    public function __construct(
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        CompaniesServiceInterface $companiesService,
        MerchantDebtorRegistrationService $merchantDebtorRegistrationService
    ) {
        $this->merchantDebtorRepository = $merchantDebtorRepository;
        $this->companiesService = $companiesService;
        $this->merchantDebtorRegistrationService = $merchantDebtorRegistrationService;
    }

    public function findDebtor(OrderContainer $orderContainer, int $merchantId): ? MerchantDebtorEntity
    {
        $this->logInfo('Check if the merchant customer already known');
        $merchantDebtor = $this->merchantDebtorRepository->getOneByMerchantExternalId(
            $orderContainer->getDebtorExternalData()->getMerchantExternalId(),
            $merchantId
        );

        if ($merchantDebtor) {
            $this->logInfo('Found the existing merchant customer');

            $debtorCompany = $this->companiesService->getDebtor($merchantDebtor->getDebtorId());
        } else {
            $this->logInfo('Start the debtor identification');

            $debtorCompany = $this->companiesService->identifyDebtor(
                (new IdentifyDebtorRequestDTO())
                    ->setName($orderContainer->getDebtorExternalData()->getName())
                    ->setHouseNumber($orderContainer->getDebtorExternalDataAddress()->getHouseNumber())
                    ->setStreet($orderContainer->getDebtorExternalDataAddress()->getStreet())
                    ->setPostalCode($orderContainer->getDebtorExternalDataAddress()->getPostalCode())
                    ->setCity($orderContainer->getDebtorExternalDataAddress()->getCity())
                    ->setCountry($orderContainer->getDebtorExternalDataAddress()->getCountry())
                    ->setTaxId($orderContainer->getDebtorExternalData()->getTaxId())
                    ->setTaxNumber($orderContainer->getDebtorExternalData()->getTaxNumber())
                    ->setRegistrationNumber($orderContainer->getDebtorExternalData()->getRegistrationNumber())
                    ->setRegistrationCourt($orderContainer->getDebtorExternalData()->getRegistrationCourt())
                    ->setLegalForm($orderContainer->getDebtorExternalData()->getLegalForm())
                    ->setFirstName($orderContainer->getDebtorPerson()->getFirstName())
                    ->setLastName($orderContainer->getDebtorPerson()->getLastName())
            );

            if ($debtorCompany) {
                $merchantDebtor = $this->merchantDebtorRepository->getOneByMerchantAndDebtorId(
                    $merchantId,
                    $debtorCompany->getId()
                );
            }
        }

        if (!$debtorCompany) {
            $this->logInfo('Debtor could not be identified');

            return null;
        }

        $this->logInfo('Debtor identified');

        if ($merchantDebtor) {
            $this->logInfo('Debtor already in the system');
        } else {
            $this->logInfo('Add new debtor to the system');

            $merchantDebtor = $this->merchantDebtorRegistrationService->registerMerchantDebtor(
                $debtorCompany->getId(),
                $orderContainer->getMerchant()
            );
        }

        $merchantDebtor->setDebtorCompany($debtorCompany);

        return $merchantDebtor;
    }
}
