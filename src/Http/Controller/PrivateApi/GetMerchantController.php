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
 *     path="/merchant/{id}",
 *     operationId="merchant_get",
 *     summary="Get Merchant",
 *
 *     tags={"Support"},
 *     x={"groups":{"private"}},
 *
 *     @OA\Parameter(in="path", name="id", @OA\Schema(type="integer"), required=true, description="Merchant ID"),
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

    public function execute(string $id): GetMerchantResponse
    {
        try {
            return $this->useCase->execute(new GetMerchantRequest((int) $id));
        } catch (MerchantNotFoundException $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        }
    }
}
