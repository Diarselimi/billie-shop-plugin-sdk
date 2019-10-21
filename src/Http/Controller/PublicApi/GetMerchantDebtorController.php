<?php

namespace App\Http\Controller\PublicApi;

use App\Application\Exception\MerchantDebtorNotFoundException;
use App\Application\UseCase\GetMerchantDebtor\GetMerchantDebtorPublicRequest;
use App\Application\UseCase\GetMerchantDebtor\GetMerchantDebtorUseCase;
use App\DomainModel\MerchantDebtorResponse\MerchantDebtor;
use App\DomainModel\MerchantDebtorResponse\MerchantDebtorResponseFactory;
use App\Http\HttpConstantsInterface;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @OA\Get(
 *     path="/debtor/{uuid}",
 *     operationId="debtor_get_details",
 *     summary="Get Debtor Details",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Dashboard Debtors"},
 *     x={"groups":{"private"}},
 *
 *     @OA\Parameter(in="path", name="uuid", @OA\Schema(ref="#/components/schemas/UUID"), required=true),
 *
 *     @OA\Response(response=200, @OA\JsonContent(ref="#/components/schemas/MerchantDebtorResponse"), description="Debtor details"),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
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

    public function execute(string $uuid, Request $request): MerchantDebtor
    {
        try {
            $container = $this->useCase->execute(new GetMerchantDebtorPublicRequest(
                $request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID),
                $uuid
            ));
        } catch (MerchantDebtorNotFoundException $e) {
            throw new NotFoundHttpException('Merchant Debtor not found.');
        }

        return $this->responseFactory->createFromContainer($container);
    }
}
