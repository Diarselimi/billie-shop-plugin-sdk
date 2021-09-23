<?php

namespace App\Http\Controller\PrivateApi;

use App\Application\CommandBus;
use App\Application\Exception\MerchantDebtorNotFoundException;
use App\Application\UseCase\UpdateMerchantDebtorLimit\UpdateMerchantDebtorLimitRequest;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @OA\Post(
 *     path="/merchant-debtor/{debtorUuid}/update-limit",
 *     operationId="update_merchant_debtor_limit",
 *     summary="Update Merchant Debtor Limit",
 *
 *     tags={"Support"},
 *     x={"groups":{"private"}},
 *
 *     @OA\Parameter(
 *          in="path",
 *          name="debtorUuid",
 *          description="Merchant Debtor UUID",
 *          @OA\Schema(ref="#/components/schemas/UUID"),
 *          required=true,
 *     ),
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json",
 *          @OA\Schema(type="object", properties={
 *              @OA\Property(property="financing_limit", type="number", format="float", description="New financing limit.")
 *          }))
 *     ),
 *
 *     @OA\Response(
 *          response=204,
 *          description="Successful response"
 *     ),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=403, ref="#/components/responses/Forbidden"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class UpdateMerchantDebtorLimitController
{
    private CommandBus $commandBus;

    public function __construct(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function execute(Request $request, string $debtorUuid): void
    {
        try {
            $this->commandBus->process(
                new UpdateMerchantDebtorLimitRequest($debtorUuid, $request->request->get('financing_limit'))
            );
        } catch (MerchantDebtorNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
    }
}
