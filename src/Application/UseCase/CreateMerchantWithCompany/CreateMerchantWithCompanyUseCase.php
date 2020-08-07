<?php

namespace App\Application\UseCase\CreateMerchantWithCompany;

use App\Application\Exception\RequestValidationException;
use App\Application\UseCase\CreateMerchant\CreateMerchantResponse;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\BankAccount\BankAccountCoreAcceptedAnnouncer;
use App\DomainModel\BankAccount\BankAccountDTOFactory;
use App\DomainModel\BankAccount\IbanDTOFactory;
use App\DomainModel\BankAccount\SepaMandateReferenceGenerator;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\CompaniesServiceRequestException;
use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\Merchant\DuplicateMerchantCompanyException;
use App\DomainModel\Merchant\MerchantCreationDTO;
use App\DomainModel\Merchant\MerchantCreationService;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\Helper\Uuid\UuidGeneratorInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class CreateMerchantWithCompanyUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $companiesService;

    private $merchantCreationService;

    private $uuidGenerator;

    private $merchantRepository;

    private $bankAccountDTOFactory;

    private $announcer;

    private $mandateReferenceGenerator;

    private $ibanFactory;

    public function __construct(
        UuidGeneratorInterface $uuidGenerator,
        MerchantCreationService $merchantCreationService,
        CompaniesServiceInterface $companiesService,
        MerchantRepositoryInterface $merchantRepository,
        BankAccountDTOFactory $bankAccountDTOFactory,
        BankAccountCoreAcceptedAnnouncer $announcer,
        SepaMandateReferenceGenerator $mandateReferenceGenerator,
        IbanDTOFactory $ibanFactory
    ) {
        $this->uuidGenerator = $uuidGenerator;
        $this->merchantCreationService = $merchantCreationService;
        $this->companiesService = $companiesService;
        $this->merchantRepository = $merchantRepository;
        $this->bankAccountDTOFactory = $bankAccountDTOFactory;
        $this->announcer = $announcer;
        $this->mandateReferenceGenerator = $mandateReferenceGenerator;
        $this->ibanFactory = $ibanFactory;
    }

    public function execute(CreateMerchantWithCompanyRequest $request): CreateMerchantResponse
    {
        $this->validateRequest($request);

        $company = $this->retrieveCompanyByCrefoIdIfExists($request->getCrefoId());

        try {
            if ($company === null) {
                $company = $this->companiesService->createDebtor($request);
            }
        } catch (CompaniesServiceRequestException $exception) {
            throw $this->wrapCompaniesException($exception);
        }

        if ($this->merchantRepository->getOneByCompanyId($company->getId())) {
            throw new DuplicateMerchantCompanyException();
        }

        $merchantCreationDTO = (new MerchantCreationDTO(
            $company,
            $this->uuidGenerator->uuid4(),
            $this->uuidGenerator->uuid4(),
            $request->getMerchantFinancingLimit(),
            $request->getInitialDebtorFinancingLimit()
        ))
            ->setWebhookUrl($request->getWebhookUrl())
            ->setWebhookAuthorization($request->getWebhookAuthorization())
            ->setIsOnboardingComplete($request->isOnboardingComplete())
            ->setFeeRates($request->getFeeRates());

        $creationDTO = $this->merchantCreationService->create($merchantCreationDTO);

        $bankAccount = $this->bankAccountDTOFactory->create(
            $creationDTO->getCompany()->getName(),
            $this->ibanFactory->createFromString($request->getIban()),
            $request->getBic(),
            $creationDTO->getPaymentUuid()
        );

        $mandateReference = $this->mandateReferenceGenerator->generate();
        $this->announcer->announce($bankAccount, $mandateReference);

        return new CreateMerchantResponse(
            $creationDTO->getMerchant(),
            $creationDTO->getOauthClient()->getClientId(),
            $creationDTO->getOauthClient()->getClientSecret()
        );
    }

    private function wrapCompaniesException(CompaniesServiceRequestException $exception): \Exception
    {
        $response = $exception->getResponse();
        if (!$response || $response->getStatusCode() !== 400) {
            return new CompanyCreationException();
        }

        $payload = (array) json_decode($response->getBody(), true);
        $violationList = new ConstraintViolationList();
        foreach ($payload['properties'] ?? [] as $error) {
            $violationList->add(new ConstraintViolation($error['message'], $error['message'], [], '', $error['name'], ''));
        }

        return new RequestValidationException($violationList);
    }

    private function retrieveCompanyByCrefoIdIfExists(?string $crefoId): ?DebtorCompany
    {
        if ($crefoId === null) {
            return null;
        }

        $companies = $this->companiesService->getDebtorsByCrefoId($crefoId);

        if (empty($companies)) {
            return null;
        }

        if (count($companies) > 1) {
            throw new DuplicateMerchantCompanyException('There are multiple companies with the same crefo ID');
        }

        return array_shift($companies);
    }
}
