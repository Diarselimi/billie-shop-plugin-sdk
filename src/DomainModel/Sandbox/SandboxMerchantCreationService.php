<?php

declare(strict_types=1);

namespace App\DomainModel\Sandbox;

use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantRepository;
use App\DomainModel\Merchant\MerchantWithCompanyCreationDTO;
use App\Helper\Payment\IbanGenerator;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class SandboxMerchantCreationService implements LoggingInterface
{
    use LoggingTrait;

    private const SANDBOX_MERCHANT_LIMIT = 1000000;

    private const SANDBOX_MERCHANT_DEBTOR_LIMIT = 10000;

    private $merchantRepository;

    private $sandboxClient;

    private $companiesService;

    private $ibanGenerator;

    public function __construct(
        MerchantRepository $merchantRepository,
        SandboxClientInterface $sandboxClient,
        CompaniesServiceInterface $companiesService,
        IbanGenerator $ibanGenerator
    ) {
        $this->merchantRepository = $merchantRepository;
        $this->sandboxClient = $sandboxClient;
        $this->companiesService = $companiesService;
        $this->ibanGenerator = $ibanGenerator;
    }

    public function create(MerchantEntity $merchant): void
    {
        $company = $this->companiesService->getDebtor((int) $merchant->getCompanyId());
        $creationDTO = (new MerchantWithCompanyCreationDTO())
            ->setIsOnboardingComplete(true)
            ->setMerchantFinancingLimit(self::SANDBOX_MERCHANT_LIMIT)
            ->setInitialDebtorFinancingLimit(self::SANDBOX_MERCHANT_DEBTOR_LIMIT)
            ->setIban($this->ibanGenerator->iban('DE', null, 24))
            ->setBic($this->ibanGenerator->bic())
            ->setName($merchant->getName())
            ->setSchufaId($company->getSchufaId())
            ->setCrefoId($company->getCrefoId())
            ->setAddressStreet($company->getAddressStreet())
            ->setAddressHouse($company->getAddressHouse())
            ->setAddressPostalCode($company->getAddressPostalCode())
            ->setAddressCity($company->getAddressCity())
            ->setAddressCountry($company->getAddressCountry())
            ->setLegalForm($company->getLegalForm())
        ;

        try {
            /** @var MerchantWithCompanyCreationDTO $creationDTO */
            $sandboxClientDTO = $this->sandboxClient->createMerchant($creationDTO);
        } catch (SandboxServiceRequestException $exception) {
            $this->logSuppressedException($exception, 'Merchant sandbox creation failed', [
                'exception' => $exception,
                'merchant_id' => $merchant->getId(),
            ]);

            throw new SandboxCreationException();
        }

        $merchant->setSandboxPaymentUuid($sandboxClientDTO->getMerchant()->getPaymentUuid());
        $this->merchantRepository->update($merchant);
    }
}
