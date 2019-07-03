<?php

namespace App\Http\Controller;

use App\Application\UseCase\GetMerchant\GetMerchantRequest;
use App\Application\UseCase\GetMerchant\GetMerchantResponse;
use App\Application\UseCase\GetMerchant\GetMerchantUseCase;
use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *     path="/merchant/{apiKey}",
 *     operationId="merchant_get",
 *     summary="Get Merchant",
 *
 *     tags={"Merchants"},
 *     x={"groups":{"support"}},
 *
 *     @OA\Parameter(in="path", name="apiKey", @OA\Schema(type="string"), required=true, description="Merchant API Key"),
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

    public function execute(string $apiKey): GetMerchantResponse
    {
        $request = new GetMerchantRequest($apiKey);
        $response = $this->useCase->execute($request);

        return $response;
    }
}
