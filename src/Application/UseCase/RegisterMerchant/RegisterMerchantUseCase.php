<?php

declare(strict_types=1);

namespace App\Application\UseCase\RegisterMerchant;

use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\Merchant\DuplicateMerchantCompanyException;
use App\DomainModel\Merchant\MerchantCompanyNotFoundException;
use App\DomainModel\Merchant\MerchantCreationDTO;
use App\DomainModel\Merchant\MerchantCreationService;
use App\DomainModel\MerchantUser\MerchantUserDefaultRoles;
use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationPersistenceService;
use App\Helper\Uuid\UuidGeneratorInterface;
use App\Infrastructure\Repository\MerchantRepository;

class RegisterMerchantUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private const INVITATION_EXPIRES_AT = '+1 year';

    private const INITIAL_MERCHANT_LIMIT = 0;

    private const INITIAL_DEBTOR_LIMIT = 0;

    private $companiesService;

    private $merchantRepository;

    private $uuidGenerator;

    private $merchantCreationService;

    private $invitationPersistenceService;

    public function __construct(
        UuidGeneratorInterface $uuidGenerator,
        CompaniesServiceInterface $companiesService,
        MerchantRepository $merchantRepository,
        MerchantCreationService $merchantCreationService,
        MerchantUserInvitationPersistenceService $invitationPersistenceService
    ) {
        $this->uuidGenerator = $uuidGenerator;
        $this->merchantCreationService = $merchantCreationService;
        $this->companiesService = $companiesService;
        $this->merchantRepository = $merchantRepository;
        $this->invitationPersistenceService = $invitationPersistenceService;
    }

    public function execute(RegisterMerchantRequest $request): RegisterMerchantResponse
    {
        $this->validateRequest($request);

        $companies = $this->companiesService->getDebtorsByCrefoId($request->getCrefoId());

        if (empty($companies)) {
            throw new MerchantCompanyNotFoundException('Cannot find a company with the given crefo ID');
        }

        if (count($companies) > 1) {
            throw new DuplicateMerchantCompanyException('There are multiple companies with the same crefo ID');
        }

        $company = array_shift($companies);

        if ($this->merchantRepository->getOneByCompanyId($company->getId())) {
            throw new DuplicateMerchantCompanyException();
        }

        $creationDTO = $this->merchantCreationService->create(new MerchantCreationDTO(
            $company,
            $this->uuidGenerator->uuid4(),
            $this->uuidGenerator->uuid4(),
            self::INITIAL_MERCHANT_LIMIT,
            self::INITIAL_DEBTOR_LIMIT,
            self::INITIAL_DEBTOR_LIMIT
        ));

        $invitation = $this->invitationPersistenceService->createInvitationByRoleName(
            MerchantUserDefaultRoles::ROLE_ADMIN['name'],
            $creationDTO->getMerchant()->getId(),
            $request->getEmail(),
            (new \DateTime())->modify(self::INVITATION_EXPIRES_AT)
        );

        return new RegisterMerchantResponse($creationDTO->getMerchant(), $invitation);
    }
}
