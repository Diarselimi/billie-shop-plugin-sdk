<?php

namespace App\Application\UseCase\RequestDebtorInformationChange;

use App\Application\Exception\CompanyNotFoundException;
use App\Application\Exception\MerchantDebtorNotFoundException;
use App\Application\Exception\RequestValidationException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\CompaniesServiceRequestException;
use App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestApprover\DebtorInformationChangeRequestAutomaticApprover;
use App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestApprover\DebtorInformationChangeRequestManualApprover;
use App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestEntity;
use App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestEntityFactory;
use App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestRepositoryInterface;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\Helper\Uuid\UuidGeneratorInterface;

class RequestDebtorInformationChangeUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $merchantDebtorRepo;

    private $debtorInformationChangeRequestRepo;

    private $changeRequestEntityFactory;

    private $uuidGenerator;

    private $companiesService;

    private $manualApprover;

    private $automaticApprover;

    public function __construct(
        MerchantDebtorRepositoryInterface $merchantDebtorRepo,
        DebtorInformationChangeRequestRepositoryInterface $debtorInformationChangeRequestRepo,
        DebtorInformationChangeRequestEntityFactory $changeRequestEntityFactory,
        UuidGeneratorInterface $uuidGenerator,
        CompaniesServiceInterface $companiesService,
        DebtorInformationChangeRequestManualApprover $manualApprover,
        DebtorInformationChangeRequestAutomaticApprover $automaticApprover
    ) {
        $this->merchantDebtorRepo = $merchantDebtorRepo;
        $this->debtorInformationChangeRequestRepo = $debtorInformationChangeRequestRepo;
        $this->changeRequestEntityFactory = $changeRequestEntityFactory;
        $this->uuidGenerator = $uuidGenerator;
        $this->companiesService = $companiesService;
        $this->manualApprover = $manualApprover;
        $this->automaticApprover = $automaticApprover;
    }

    public function execute(RequestDebtorInformationChangeRequest $request): void
    {
        $this->validateRequest($request);

        $merchantDebtor = $this->merchantDebtorRepo->getOneByUuid($request->getDebtorUuid());

        if (!$merchantDebtor) {
            throw new MerchantDebtorNotFoundException();
        }

        try {
            $debtorCompany = $this->companiesService->getDebtorByUuid($merchantDebtor->getCompanyUuid());

            if (!$debtorCompany) {
                throw new CompanyNotFoundException();
            }
        } catch (CompaniesServiceRequestException $exception) {
            throw new CompanyNotFoundException($exception->getMessage(), null, $exception);
        }

        $debtorInformationChangeRequestEntity = $this->changeRequestEntityFactory->createFromArray([
            'uuid' => $this->uuidGenerator->uuid4(),
            'company_uuid' => $merchantDebtor->getCompanyUuid(),
            'name' => $request->getName(),
            'city' => $request->getCity(),
            'postal_code' => $request->getPostalCode(),
            'street' => $request->getStreet(),
            'house_number' => $request->getHouseNumber(),
            'merchant_user_id' => $request->getMerchantUserId(),
            'is_seen' => false,
            'state' => DebtorInformationChangeRequestEntity::INITIAL_STATE,
            'created_at' => 'now',
            'updated_at' => 'now',
        ]);

        $this->debtorInformationChangeRequestRepo->insert($debtorInformationChangeRequestEntity);

        if ($debtorCompany->getName() !== $request->getName()) {
            $this->manualApprover->approve($debtorInformationChangeRequestEntity);
        } else {
            $this->automaticApprover->approve($debtorInformationChangeRequestEntity);
        }
    }
}
