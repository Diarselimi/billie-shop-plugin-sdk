<?php

declare(strict_types=1);

namespace App\DomainModel\IdentityVerification;

use App\Application\UseCase\StartIdentityVerification\StartIdentityVerificationException;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\CompaniesServiceRequestException;
use App\Http\Authentication\UserProvider;

class StartIdentityVerificationGuard
{
    private $companiesService;

    private $userProvider;

    public function __construct(
        CompaniesServiceInterface $companiesService,
        UserProvider $userProvider
    ) {
        $this->companiesService = $companiesService;
        $this->userProvider = $userProvider;
    }

    public function startIdentityVerificationAllowed(): bool
    {
        $caseUuid = $this->userProvider->getMerchantUser()->getUserEntity()->getIdentityVerificationCaseUuid();

        try {
            $identityVerificationCaseDTO = $this->companiesService->getIdentityVerificationCase($caseUuid);
        } catch (CompaniesServiceRequestException $exception) {
            throw new StartIdentityVerificationException();
        }

        if (!$identityVerificationCaseDTO->isValid()) {
            return true;
        }

        return false;
    }
}
