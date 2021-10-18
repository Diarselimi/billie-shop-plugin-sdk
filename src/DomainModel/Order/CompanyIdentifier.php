<?php

namespace App\DomainModel\Order;

use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\IdentifyDebtorRequestDTO;
use App\DomainModel\DebtorCompany\IdentifyDebtorRequestFactory;
use App\DomainModel\MerchantDebtor\Finder\MerchantDebtorFinder;
use App\DomainModel\MerchantDebtor\Finder\MerchantDebtorFinderResult;
use App\DomainModel\MerchantDebtor\MerchantDebtorRegistrationService;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class CompanyIdentifier implements LoggingInterface
{
    use LoggingTrait;

    private MerchantDebtorFinder $debtorFinderService;

    private MerchantDebtorRepositoryInterface $merchantDebtorRepository;

    private CompaniesServiceInterface $companiesService;

    private IdentifyDebtorRequestFactory $identifyDebtorRequestFactory;

    private MerchantDebtorRegistrationService $merchantDebtorRegistrationService;

    public function __construct(
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        CompaniesServiceInterface $companiesService,
        MerchantDebtorFinder $debtorFinderService,
        IdentifyDebtorRequestFactory $identifyDebtorRequestFactory,
        MerchantDebtorRegistrationService $merchantDebtorRegistrationService
    ) {
        $this->debtorFinderService = $debtorFinderService;
        $this->merchantDebtorRepository = $merchantDebtorRepository;
        $this->companiesService = $companiesService;
        $this->identifyDebtorRequestFactory = $identifyDebtorRequestFactory;
        $this->merchantDebtorRegistrationService = $merchantDebtorRegistrationService;
    }

    public function identify(OrderContainer $orderContainer): bool
    {
        $merchantDebtorFinderResult = $this->debtorFinderService->findDebtorBasedOnPreviousOrderCreationAttempts($orderContainer);

        $identifyRequest = $this->identifyDebtorRequestFactory->createDebtorRequestDTO($orderContainer);
        if ($merchantDebtorFinderResult->getMerchantDebtor() !== null) {
            $identifyRequest->setCompanyId((int) $merchantDebtorFinderResult->getMerchantDebtor()->getDebtorId());
            $identifyRequest->setCompanyUuid($merchantDebtorFinderResult->getMerchantDebtor()->getCompanyUuid());
        }

        if (!$merchantDebtorFinderResult->isAllPreviousOrdersDeclined()) {
            $merchantDebtorFinderResult = $this->identifyAsNewDebtor($orderContainer, $identifyRequest);
        }

        $orderContainer
            ->setMostSimilarCandidateDTO($merchantDebtorFinderResult->getMostSimilarCandidateDTO());

        if ($merchantDebtorFinderResult->getMerchantDebtor() === null) {
            return false;
        }

        $orderContainer
            ->setMerchantDebtor($merchantDebtorFinderResult->getMerchantDebtor())
            ->setIdentifiedDebtorCompany($merchantDebtorFinderResult->getIdentifiedDebtorCompany());

        $orderContainer->getOrder()->setMerchantDebtorId($merchantDebtorFinderResult->getMerchantDebtor()->getId());
        $orderContainer->getOrder()->setCompanyBillingAddressUuid(
            $merchantDebtorFinderResult->getIdentifiedDebtorCompany()->getBillingAddressMatchUuid()
        );

        return true;
    }

    private function identifyAsNewDebtor(
        OrderContainer $orderContainer,
        IdentifyDebtorRequestDTO $identifyRequest
    ): MerchantDebtorFinderResult {
        $this->logInfo('Starting {name} debtor identification algorithm for order {number}', [
            LoggingInterface::KEY_NAME => $identifyRequest->isExperimental() ? 'experimental' : 'normal',
            LoggingInterface::KEY_NUMBER => $orderContainer->getOrder()->getExternalCode(),
        ]);

        $identifyDebtorResponseDTO = $this->companiesService->identifyDebtor($identifyRequest);
        $debtorCompany = $identifyDebtorResponseDTO->getIdentifiedDebtorCompany();
        if (!$debtorCompany) {
            $this->logInfo('Debtor could not be identified');

            return new MerchantDebtorFinderResult(null, null, $identifyDebtorResponseDTO->getMostSimilarCandidate());
        }

        $this->logInfo('Debtor identified');

        $merchantDebtor = $this->merchantDebtorRepository->getOneByMerchantAndCompanyUuid(
            $orderContainer->getOrder()->getMerchantId(),
            $debtorCompany->getUuid()
        );

        if ($merchantDebtor) {
            $this->logInfo('Merchant debtor already exists');
        } else {
            $this->logInfo('New merchant debtor created');

            $merchantDebtor = $this->merchantDebtorRegistrationService->registerMerchantDebtor(
                $debtorCompany,
                $orderContainer->getMerchant()
            );
        }

        return new MerchantDebtorFinderResult(
            $merchantDebtor,
            $debtorCompany,
            $identifyDebtorResponseDTO->getMostSimilarCandidate()
        );
    }
}
