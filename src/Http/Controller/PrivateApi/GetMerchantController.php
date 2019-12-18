<?php

namespace App\Http\Controller\PrivateApi;

use App\Application\UseCase\GetMerchant\GetMerchantRequest;
use App\Application\UseCase\GetMerchant\GetMerchantResponse;
use App\Application\UseCase\GetMerchant\GetMerchantUseCase;
use App\Application\UseCase\GetMerchant\MerchantNotFoundException;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @OA\Get(
 *     path="/merchant/{identifier}",
 *     operationId="merchant_get",
 *     summary="Get Merchant",
 *
 *     tags={"Support"},
 *     x={"groups":{"private"}},
 *
 *     @OA\Parameter(in="path", name="identifier", @OA\Schema(type="string"), required=true, description="Merchant ID or Merchant Payment Uuid"),
 *
 *     @OA\Response(response=200, @OA\JsonContent(ref="#/components/schemas/MerchantEntity"), description="Merchant Entity"),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class GetMerchantController
{
    private $useCase;

    public function __construct(GetMerchantUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(string $identifier): GetMerchantResponse
    {
        try {
            return $this->useCase->execute(new GetMerchantRequest($identifier));
        } catch (MerchantNotFoundException $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        }
    }
}
