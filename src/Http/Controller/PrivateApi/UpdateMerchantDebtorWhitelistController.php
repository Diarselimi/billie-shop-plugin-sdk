<?php

namespace App\Http\Controller\PrivateApi;

use App\Application\Exception\MerchantDebtorNotFoundException;
use App\Application\UseCase\UpdateMerchantDebtorWhitelist\WhitelistMerchantDebtorRequest;
use App\Application\UseCase\UpdateMerchantDebtorWhitelist\WhitelistMerchantDebtorUseCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use OpenApi\Annotations as OA;

/**
 * @OA\Post(
 *     path="/merchant-debtor/{debtorUuid}/whitelist",
 *     operationId="whitelist_merchant_debtor",
 *     summary="Whitelist Merchant Debtor",
 *
 *     tags={"Debtors"},
 *     x={"groups":{"support", "salesforce"}},
 *
 *     @OA\Parameter(in="path", name="debtorUuid", description="Merchant-Debtor UUID", @OA\Schema(type="string"), required=true),
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json",
 *          @OA\Schema(type="object", properties={
 *              @OA\Property(property="is_whitelisted", type="boolean", nullable=false)
 *          }))
 *     ),
 *
 *     @OA\Response(response=204, description="Merchant debtor is whitelisted"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class UpdateMerchantDebtorWhitelistController
{
    private $useCase;

    public function __construct(WhitelistMerchantDebtorUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(Request $request, string $debtorUuid): void
    {
        try {
            $this->useCase->execute(
                new WhitelistMerchantDebtorRequest(
                    $debtorUuid,
                    $request->request->getBoolean('is_whitelisted')
                )
            );
        } catch (MerchantDebtorNotFoundException $exception) {
            throw new NotFoundHttpException($exception->getMessage(), null, Response::HTTP_NOT_FOUND);
        }
    }
}
