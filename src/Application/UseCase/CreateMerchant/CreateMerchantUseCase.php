<?php

namespace App\Application\UseCase\CreateMerchant;

use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\Merchant\DuplicateMerchantCompanyException;
use App\DomainModel\Merchant\MerchantCompanyNotFoundException;
use App\DomainModel\Merchant\MerchantCreationDTO;
use App\DomainModel\Merchant\MerchantCreationService;
use App\DomainModel\Merchant\MerchantRepository;
use App\Helper\Uuid\UuidGeneratorInterface;

class CreateMerchantUseCase
{
    private $uuidGenerator;

    private $merchantRepository;

    private $companiesService;

    private $merchantCreationService;

    public function __construct(
        UuidGeneratorInterface $uuidGenerator,
        MerchantRepository $merchantRepository,
        CompaniesServiceInterface $companiesService,
        MerchantCreationService $merchantCreationService
    ) {
        $this->uuidGenerator = $uuidGenerator;
        $this->merchantRepository = $merchantRepository;
        $this->companiesService = $companiesService;
        $this->merchantCreationService = $merchantCreationService;
    }

    public function execute(CreateMerchantRequest $request): CreateMerchantResponse
    {
        if ($this->merchantRepository->getOneByCompanyId($request->getCompanyId())) {
            throw new DuplicateMerchantCompanyException();
        }

        $company = $this->companiesService->getDebtor($request->getCompanyId());

        if (!$company) {
            throw new MerchantCompanyNotFoundException();
        }

        $creationDTO = $this->merchantCreationService->create(
            (new MerchantCreationDTO(
                $company,
                $this->uuidGenerator->uuid4(),
                $this->uuidGenerator->uuid4(),
                $request->getMerchantFinancingLimit(),
                $request->getInitialDebtorFinancingLimit()
            ))
                ->setWebhookUrl($request->getWebhookUrl())
                ->setWebhookAuthorization($request->getWebhookAuthorization())
                ->setIsOnboardingComplete(false)
        );

        return new CreateMerchantResponse(
            $creationDTO->getMerchant(),
            $creationDTO->getOauthClient()->getClientId(),
            $creationDTO->getOauthClient()->getClientSecret()
        );
    }
}
