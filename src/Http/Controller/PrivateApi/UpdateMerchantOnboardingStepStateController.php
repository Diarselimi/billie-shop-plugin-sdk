<?php

namespace App\Http\Controller\PrivateApi;

use App\Application\UseCase\UpdateMerchantOnboardingStepState\UpdateMerchantOnboardingStepStateRequest;
use App\Application\UseCase\UpdateMerchantOnboardingStepState\UpdateMerchantOnboardingStepStateUseCase;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Request;

/**
 * @OA\Post(
 *     path="/merchant/{uuid}/onboarding-step/state",
 *     operationId="merchant_onboarding_step_state_update",
 *     summary="Update Merchant Onboarding step state",
 *
 *     tags={"Dashboard Merchants"},
 *     x={"groups":{"private"}},
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json",
 *          @OA\Schema(ref="#/components/schemas/UpdateMerchantOnboardingStepStateRequest"))
 *     ),
 *
 *     @OA\Response(response=204, description="State changed successfully."),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound", description="Merchant not found"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class UpdateMerchantOnboardingStepStateController
{
    private $useCase;

    public function __construct(UpdateMerchantOnboardingStepStateUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(Request $request, string $uuid): void
    {
        $useCaseRequest = new UpdateMerchantOnboardingStepStateRequest(
            $uuid,
            $request->get('step'),
            $request->get('state')
        );

        // TODO: handle exceptions
        $this->useCase->execute($useCaseRequest);
    }
}
