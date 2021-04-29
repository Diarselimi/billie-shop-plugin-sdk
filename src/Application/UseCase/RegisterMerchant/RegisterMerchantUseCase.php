<?php

declare(strict_types=1);

namespace App\Application\UseCase\RegisterMerchant;

use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\CompaniesServiceRequestException;
use App\DomainModel\Merchant\DuplicateMerchantCompanyException;
use App\DomainModel\DebtorCompany\IdentifyFirmenwissenFailedException;
use App\DomainModel\Merchant\MerchantCreationDTO;
use App\DomainModel\Merchant\MerchantCreationService;
use App\DomainModel\MerchantUser\MerchantUserDefaultRoles;
use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationPersistenceService;
use App\Helper\Uuid\UuidGeneratorInterface;
use App\Infrastructure\Repository\MerchantRepository;
use Ramsey\Uuid\Uuid;

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
            try {
                $companies = [$this->companiesService->identifyFirmenwissen($request->getCrefoId())];
            } catch (CompaniesServiceRequestException $exception) {
                throw new IdentifyFirmenwissenFailedException($exception->getMessage(), 0, $exception);
            }
        }

        if (count($companies) > 1) {
            throw new DuplicateMerchantCompanyException('There are multiple companies with the same crefo ID');
        }

        $company = array_shift($companies);

        if ($this->merchantRepository->getOneByCompanyId($company->getId())) {
            throw new DuplicateMerchantCompanyException();
        }

        $creationDTO = new MerchantCreationDTO(
            $company,
            $this->uuidGenerator->uuid4(),
            $this->uuidGenerator->uuid4(),
            self::INITIAL_MERCHANT_LIMIT,
            self::INITIAL_DEBTOR_LIMIT
        );
        $creationDTO->setIsOnboardingComplete(false);
        $this->merchantCreationService->create($creationDTO);

        $invitation = $this->invitationPersistenceService->createInvitationByRoleName(
            MerchantUserDefaultRoles::ROLE_ADMIN['name'],
            $creationDTO->getMerchant(),
            $request->getEmail(),
            (new \DateTime())->modify(self::INVITATION_EXPIRES_AT)
        );

        return new RegisterMerchantResponse($creationDTO->getMerchant(), $invitation);
    }
}
