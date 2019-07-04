<?php

namespace App\Http\Controller\PrivateApi;

use App\Application\UseCase\CreateMerchant\CreateMerchantRequest;
use App\Application\UseCase\CreateMerchant\CreateMerchantUseCase;
use App\Application\UseCase\CreateMerchant\Exception\CreateMerchantException;
use App\Application\UseCase\CreateMerchant\Exception\DuplicateMerchantCompanyException;
use App\Application\UseCase\CreateMerchant\Exception\MerchantCompanyNotFoundException;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @OA\Post(
 *     path="/merchant",
 *     operationId="merchant_create",
 *     summary="Create Merchant",
 *
 *     tags={"Merchants"},
 *     x={"groups":{"support"}},
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
 *     @OA\Response(response=409, @OA\JsonContent(ref="#/components/schemas/AbstractErrorObject"), description="Request conflicts with the current state of the server."),
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
                ->setCompanyId($request->request->get('company_id'))
                ->setMerchantFinancingLimit($request->request->get('merchant_financing_limit'))
                ->setInitialDebtorFinancingLimit($request->request->get('initial_debtor_financing_limit'))
                ->setDebtorFinancingLimit($request->request->get('debtor_financing_limit'))
                ->setWebhookUrl($request->request->get('webhook_url'))
                ->setWebhookAuthorization($request->request->get('webhook_authorization'));

            $response = $this->useCase->execute($request);

            return new JsonResponse($response->toArray(), JsonResponse::HTTP_CREATED);
        } catch (DuplicateMerchantCompanyException $e) {
            throw new ConflictHttpException($e->getMessage());
        } catch (MerchantCompanyNotFoundException $e) {
            throw new BadRequestHttpException($e->getMessage());
        } catch (CreateMerchantException $e) {
            throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage());
        }
    }
}
