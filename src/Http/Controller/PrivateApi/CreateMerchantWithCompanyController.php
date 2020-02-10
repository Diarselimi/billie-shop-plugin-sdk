<?php

namespace App\Http\Controller\PrivateApi;

use App\Application\UseCase\CreateMerchantWithCompany\CompanyCreationException;
use App\Application\UseCase\CreateMerchantWithCompany\CreateMerchantWithCompanyRequest;
use App\Application\UseCase\CreateMerchantWithCompany\CreateMerchantWithCompanyUseCase;
use App\DomainModel\Merchant\CreateMerchantException;
use App\DomainModel\Merchant\DuplicateMerchantCompanyException;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

/**
 * @OA\Post(
 *     path="/merchant/with-company",
 *     operationId="create_merchant_with_company",
 *     summary="Create Merchant with Company",
 *     description="Creates a new Merchant and a new Company associated to it.
 *          No company identification process is triggered, the company is created directly with the provided data as-it-is.",
 *
 *     tags={"Support"},
 *     x={"groups":{"private"}},
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/CreateMerchantWithCompanyRequest"))
 *     ),
 *
 *     @OA\Response(
 *          response=200,
 *          description="Successful response",
 *          @OA\JsonContent(ref="#/components/schemas/CreateMerchantResponse")
 *     ),
 *
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=403, ref="#/components/responses/Forbidden"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class CreateMerchantWithCompanyController
{
    private $useCase;

    public function __construct(CreateMerchantWithCompanyUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(Request $request): JsonResponse
    {
        $useCaseRequest = (new CreateMerchantWithCompanyRequest())
            ->setMerchantFinancingLimit($request->get('merchant_financing_limit', 0))
            ->setInitialDebtorFinancingLimit($request->get('initial_debtor_financing_limit', 0))
            ->setIban($request->get('iban'))
            ->setBic($request->get('bic'))
            ->setWebhookUrl($request->get('webhook_url'))
            ->setWebhookAuthorization($request->get('webhook_authorization'))
            ->setIsOnboardingComplete($request->get('is_onboarding_complete', false))
            ->setName($request->get('name'))
            ->setLegalForm($request->get('legal_form'))
            ->setAddressHouse($request->get('address_house'))
            ->setAddressStreet($request->get('address_street'))
            ->setAddressCity($request->get('address_city'))
            ->setAddressPostalCode($request->get('address_postal_code'))
            ->setAddressCountry($request->get('address_country'))
            ->setCrefoId($request->get('crefo_id'))
            ->setSchufaId($request->get('schufa_id'))
            ->setTaxId($request->get('tax_id'))
            ->setRegistrationNumber($request->get('registration_number'));

        try {
            /** @var CreateMerchantWithCompanyRequest $useCaseRequest */
            $response = $this->useCase->execute($useCaseRequest);

            return new JsonResponse($response->toArray(), JsonResponse::HTTP_CREATED);
        } catch (DuplicateMerchantCompanyException $e) {
            throw new ConflictHttpException($e->getMessage(), $e);
        } catch (CreateMerchantException | CompanyCreationException $e) {
            throw new ServiceUnavailableHttpException(null, $e->getMessage(), $e);
        }
    }
}
