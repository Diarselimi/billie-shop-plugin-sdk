<?php

namespace App\Http\Controller\PrivateApi;

use App\Application\UseCase\UpdateMerchantState\UpdateMerchantStateRequest;
use App\Application\UseCase\UpdateMerchantState\UpdateMerchantStateUseCase;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Request;

/**
 * @OA\Post(
 *     path="/merchant/{uuid}/state",
 *     operationId="merchant_state_transition_update",
 *     summary="Update Merchant State",
 *
 *     tags={"Dashboard Merchants"},
 *     x={"groups":{"private"}},
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json",
 *          @OA\Schema(ref="#/components/schemas/UpdateMerchantStateRequest"))
 *     ),
 *
 *     @OA\Response(response=204, description="State changed successfully."),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound", description="Merchant not found"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class UpdateMerchantStateController
{
    private $useCase;

    public function __construct(UpdateMerchantStateUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(Request $request, string $uuid): void
    {
        $useCaseRequest = new UpdateMerchantStateRequest($uuid, $request->get('state'));

        // TODO: handle exceptions
        $this->useCase->execute($useCaseRequest);
    }
}
