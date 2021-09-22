<?php

namespace App\DomainModel\MerchantUser;

use App\DomainModel\Address\AddressEntityFactory;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\CompaniesServiceRequestException;
use App\DomainModel\Merchant\MerchantCompanyNotFoundException;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingContainer;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingContainerFactory;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingTransitionEntity;

class MerchantUserService
{
    protected $merchantUserRepository;

    protected $merchantRepository;

    protected $companiesService;

    protected $addressEntityFactory;

    protected $merchantUserPermissionsService;

    private $onboardingContainerFactory;

    public function __construct(
        MerchantUserRepositoryInterface $merchantUserRepository,
        MerchantRepositoryInterface $merchantRepository,
        CompaniesServiceInterface $companiesService,
        AddressEntityFactory $addressEntityFactory,
        MerchantUserPermissionsService $merchantUserPermissionsService,
        MerchantOnboardingContainerFactory $onboardingContainerFactory
    ) {
        $this->merchantUserRepository = $merchantUserRepository;
        $this->merchantRepository = $merchantRepository;
        $this->companiesService = $companiesService;
        $this->addressEntityFactory = $addressEntityFactory;
        $this->merchantUserPermissionsService = $merchantUserPermissionsService;
        $this->onboardingContainerFactory = $onboardingContainerFactory;
    }

    public function getUser(string $uuid, string $email): MerchantUserDTO
    {
        $merchantUser = $this->merchantUserRepository->getOneByUuid($uuid);

        if (!$merchantUser) {
            throw new MerchantUserNotFoundException("Merchant user not found");
        }

        $merchant = $this->merchantRepository->getOneById($merchantUser->getMerchantId());

        try {
            $company = $this->companiesService->getDebtor($merchant->getCompanyId());
        } catch (CompaniesServiceRequestException $exception) {
            throw new MerchantCompanyNotFoundException("Merchant company cannot be found", 0, $exception);
        }

        if (!$company) {
            throw new MerchantCompanyNotFoundException();
        }

        $role = $this->merchantUserPermissionsService->resolveUserRole($merchantUser);
        $onboardingContainer = $this->onboardingContainerFactory->create($merchant->getId());

        return (new MerchantUserDTO())
            ->setUser($merchantUser)
            ->setEmail($email)
            ->setRole($role)
            ->setOnboardingState($onboardingContainer->getOnboarding()->getState())
            ->setOnboardingCompleteAt($this->getOnboardingCompleteAt($onboardingContainer))
            ->setMerchantCompanyName($company->getName())
            ->setMerchantCompanyAddress($this->addressEntityFactory->createFromDebtorCompany($company))
        ;
    }

    private function getOnboardingCompleteAt(MerchantOnboardingContainer $onboardingContainer): ?\DateTimeInterface
    {
        foreach ($onboardingContainer->getOnboardingTransitions() as $onboardingTransition) {
            if ($onboardingTransition->getTransition() === MerchantOnboardingTransitionEntity::TRANSITION_COMPLETE) {
                return $onboardingTransition->getTransitedAt();
            }
        }

        return null;
    }
}
