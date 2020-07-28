<?php

declare(strict_types=1);

namespace App\Application\UseCase\GetMerchantUserIdentityVerification;

use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\CompaniesServiceRequestException;
use App\DomainModel\IdentityVerification\IdentityVerificationCaseDTO;
use App\Http\Authentication\UserProvider;

class GetMerchantUserIdentityVerificationUseCase
{
    private $userProvider;

    private $companiesService;

    public function __construct(
        UserProvider $userProvider,
        CompaniesServiceInterface $companiesService
    ) {
        $this->userProvider = $userProvider;
        $this->companiesService = $companiesService;
    }

    public function execute(): IdentityVerificationCaseDTO
    {
        $caseUuid = $this->userProvider->getMerchantUser()->getUserEntity()->getIdentityVerificationCaseUuid();
        if (!$caseUuid) {
            throw new IdentityVerificationCaseNotFoundException('There is no case linked to this user');
        }

        try {
            $identityVerificationCaseDTO = $this->companiesService->getIdentityVerificationCase($caseUuid);
        } catch (CompaniesServiceRequestException $exception) {
            throw new GetMerchantUserIdentityVerificationUseCaseException($exception->getMessage());
        }

        if (!$identityVerificationCaseDTO->isValid()) {
            throw new IdentityVerificationCaseNotFoundException('There is no valid case linked to this user');
        }

        return $identityVerificationCaseDTO;
    }
}
