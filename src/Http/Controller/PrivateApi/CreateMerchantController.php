<?php

namespace App\Http\Controller\PrivateApi;

use App\Application\UseCase\CreateMerchant\CreateMerchantRequest;
use App\Application\UseCase\CreateMerchant\CreateMerchantUseCase;
use App\DomainModel\Merchant\CreateMerchantException;
use App\DomainModel\Merchant\DuplicateMerchantCompanyException;
use App\DomainModel\Merchant\MerchantCompanyNotFoundException;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

/**
 * @OA\Post(
 *     path="/merchant",
 *     operationId="merchant_create",
 *     summary="Create Merchant",
 *
 *     tags={"Support"},
 *     x={"groups":{"private"}},
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json",
 *          @OA\Schema(ref="#/components/schemas/CreateMerchantRequest"))
 *     ),
 *
 *     @OA\Response(response=201, description="Merchant successfully created", @OA\JsonContent(ref="#/components/schemas/CreateMerchantResponse")),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=409, @OA\JsonContent(ref="#/components/schemas/ErrorsObject"), description="Request conflicts with the current state of the server."),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class CreateMerchantController
{
    private $useCase;

    public function __construct(CreateMerchantUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(Request $request): JsonResponse
    {
        try {
            $request = (new CreateMerchantRequest())
                ->setCompanyId($request->get('company_id'))
                ->setMerchantFinancingLimit($request->get('merchant_financing_limit'))
                ->setInitialDebtorFinancingLimit($request->get('initial_debtor_financing_limit'))
                ->setWebhookUrl($request->get('webhook_url'))
                ->setWebhookAuthorization($request->get('webhook_authorization'));

            $response = $this->useCase->execute($request);

            return new JsonResponse($response->toArray(), JsonResponse::HTTP_CREATED);
        } catch (DuplicateMerchantCompanyException $e) {
            throw new ConflictHttpException($e->getMessage());
        } catch (MerchantCompanyNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        } catch (CreateMerchantException $e) {
            throw new ServiceUnavailableHttpException(null, $e->getMessage());
        }
    }
}
