<?php

namespace App\Http\Controller\PrivateApi\Dashboard;

use App\Application\Exception\WorkflowException;
use App\Application\UseCase\GetMerchant\MerchantNotFoundException;
use App\Application\UseCase\MerchantOnboardingStepTransition\MerchantOnboardingStepTransitionRequest;
use App\Application\UseCase\MerchantOnboardingStepTransition\MerchantOnboardingStepTransitionUseCase;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepNotFoundException;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @OA\Post(
 *     path="/merchant/{uuid}/onboarding-step/transition",
 *     operationId="merchant_onboarding_step_transition_update",
 *     summary="Merchant Onboarding Step Transition",
 *
 *     tags={"Dashboard Merchants"},
 *     x={"groups":{"private"}},
 *
 *     @OA\Parameter(
 *          in="path",
 *          name="uuid",
 *          @OA\Schema(ref="#/components/schemas/UUID"),
 *          description="Merchant Payment UUID",
 *          required=true
 *     ),
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json",
 *          @OA\Schema(ref="#/components/schemas/MerchantOnboardingStepTransitionRequest"))
 *     ),
 *
 *     @OA\Response(response=204, description="State changed successfully."),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest", description="Transition not supported"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound", description="Step not found"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class MerchantOnboardingStepTransitionController
{
    private $useCase;

    public function __construct(MerchantOnboardingStepTransitionUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(Request $request, string $uuid): void
    {
        $useCaseRequest = new MerchantOnboardingStepTransitionRequest(
            $uuid,
            $request->get('step'),
            $request->get('transition')
        );

        try {
            $this->useCase->execute($useCaseRequest);
        } catch (MerchantOnboardingStepNotFoundException $exception) {
            throw new NotFoundHttpException('Onboarding step not found', $exception);
        } catch (MerchantNotFoundException $exception) {
            throw new NotFoundHttpException('Merchant not found', $exception);
        } catch (WorkflowException $exception) {
            throw new BadRequestHttpException('Transition not supported', $exception);
        }
    }
}
