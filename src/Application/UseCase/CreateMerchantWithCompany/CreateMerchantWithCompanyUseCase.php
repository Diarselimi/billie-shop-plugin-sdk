<?php

namespace App\Application\UseCase\CreateMerchantWithCompany;

use App\Application\Exception\RequestValidationException;
use App\Application\UseCase\CreateMerchant\CreateMerchantResponse;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\CompaniesServiceRequestException;
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

    /**
     * @var CompaniesServiceInterface
     */
    private $companiesService;

    /**
     * @var MerchantCreationService
     */
    private $merchantCreationService;

    /**
     * @var UuidGeneratorInterface
     */
    private $uuidGenerator;

    /**
     * @var MerchantRepositoryInterface
     */
    private $merchantRepository;

    public function __construct(
        UuidGeneratorInterface $uuidGenerator,
        MerchantCreationService $merchantCreationService,
        CompaniesServiceInterface $companiesService,
        MerchantRepositoryInterface $merchantRepository
    ) {
        $this->uuidGenerator = $uuidGenerator;
        $this->merchantCreationService = $merchantCreationService;
        $this->companiesService = $companiesService;
        $this->merchantRepository = $merchantRepository;
    }

    public function execute(CreateMerchantWithCompanyRequest $request): CreateMerchantResponse
    {
        $this->validateRequest($request);

        try {
            $company = $this->companiesService->createDebtor($request);
        } catch (CompaniesServiceRequestException $exception) {
            throw $this->wrapCompaniesException($exception);
        }

        if ($this->merchantRepository->getOneByCompanyId($company->getId())) {
            throw new DuplicateMerchantCompanyException();
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
                ->setIsOnboardingComplete($request->isOnboardingComplete())
        );

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
}
