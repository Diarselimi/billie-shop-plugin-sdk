<?php

namespace App\Http\Controller;

use App\Application\Exception\MerchantDebtorNotFoundException;
use App\Application\UseCase\GetMerchantDebtor\GetMerchantDebtorRequest;
use App\Application\UseCase\GetMerchantDebtor\GetMerchantDebtorUseCase;
use App\DomainModel\MerchantDebtorResponse\MerchantDebtorExtended;
use App\DomainModel\MerchantDebtorResponse\MerchantDebtorResponseFactory;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *     path="/merchant/{merchantId}/merchant-debtor/{merchantDebtorExternalId}",
 *     operationId="debtor_get_details_extended",
 *     summary="Get Debtor Details (Extended)",
 *
 *     tags={"Internal API"},
 *     x={"groups":{"private"}},
 *
 *     @OA\Parameter(in="path", name="merchantId", @OA\Schema(type="integer"), required=true),
 *     @OA\Parameter(in="path", name="merchantDebtorExternalId", @OA\Schema(type="string"), required=true),
 *
 *     @OA\Response(response=200, @OA\JsonContent(ref="#/components/schemas/MerchantDebtorExtendedResponse"), description="Debtor Extended Info"),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class GetMerchantDebtorSupportController
{
    private $useCase;

    private $responseFactory;

    public function __construct(GetMerchantDebtorUseCase $useCase, MerchantDebtorResponseFactory $responseFactory)
    {
        $this->useCase = $useCase;
        $this->responseFactory = $responseFactory;
    }

    public function execute(int $merchantId, string $merchantDebtorExternalId): MerchantDebtorExtended
    {
        try {
            $container = $this->useCase->execute(new GetMerchantDebtorRequest($merchantId, null, $merchantDebtorExternalId));
        } catch (MerchantDebtorNotFoundException $e) {
            throw new NotFoundHttpException('Merchant Debtor not found.');
        }

        return $this->responseFactory->createExtendedFromContainer($container);
    }
}
