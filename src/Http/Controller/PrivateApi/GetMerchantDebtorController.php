<?php

namespace App\Http\Controller\PrivateApi;

use App\Application\Exception\MerchantDebtorNotFoundException;
use App\Application\UseCase\GetMerchantDebtor\GetMerchantDebtorRequest;
use App\Application\UseCase\GetMerchantDebtor\GetMerchantDebtorUseCase;
use App\DomainModel\MerchantDebtorResponse\MerchantDebtorExtended;
use App\DomainModel\MerchantDebtorResponse\MerchantDebtorResponseFactory;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @OA\Get(
 *     path="/merchant-debtor/{debtorUuid}",
 *     operationId="debtor_get_details_extended",
 *     summary="Get Debtor Details (Extended)",
 *
 *     tags={"Debtors"},
 *     x={"groups":{"support"}},
 *
 *     @OA\Parameter(in="path", name="merchantId", @OA\Schema(type="integer"), required=true),
 *     @OA\Parameter(in="path", name="debtorUuid", description="Merchant-Debtor UUID", @OA\Schema(type="string"), required=true),
 *
 *     @OA\Response(response=200, @OA\JsonContent(ref="#/components/schemas/MerchantDebtorExtendedResponse"), description="Debtor Extended Info"),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class GetMerchantDebtorController
{
    private $useCase;

    private $responseFactory;

    public function __construct(GetMerchantDebtorUseCase $useCase, MerchantDebtorResponseFactory $responseFactory)
    {
        $this->useCase = $useCase;
        $this->responseFactory = $responseFactory;
    }

    public function execute(string $debtorUuid): MerchantDebtorExtended
    {
        try {
            $container = $this->useCase->execute(new GetMerchantDebtorRequest(null, $debtorUuid));
        } catch (MerchantDebtorNotFoundException $e) {
            throw new NotFoundHttpException('Merchant Debtor not found.');
        }

        return $this->responseFactory->createExtendedFromContainer($container);
    }
}
