<?php

namespace App\Http\Controller\PrivateApi;

use App\Application\Exception\WorkflowException;
use App\Application\UseCase\MerchantOnboardingTransition\MerchantOnboardingNotFoundException;
use App\Application\UseCase\MerchantOnboardingTransition\MerchantOnboardingStepsIncompleteException;
use App\Application\UseCase\MerchantOnboardingTransition\MerchantOnboardingTransitionRequest;
use App\Application\UseCase\MerchantOnboardingTransition\MerchantOnboardingTransitionUseCase;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @OA\Post(
 *     path="/merchant/{uuid}/onboarding/transition",
 *     operationId="merchant_onboarding_transition_change",
 *     summary="Merchant Onboarding Transition",
 *
 *     tags={"Dashboard Merchants"},
 *     x={"groups":{"private"}},
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json",
 *          @OA\Schema(ref="#/components/schemas/MerchantOnboardingTransitionRequest"))
 *     ),
 *
 *     @OA\Response(response=204, description="Onboarding state changed successfully."),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest", description="Transition not supported"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound", description="Merchant not found"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class MerchantOnboardingTransitionController
{
    private $useCase;

    public function __construct(MerchantOnboardingTransitionUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(Request $request, string $uuid): void
    {
        $useCaseRequest = new MerchantOnboardingTransitionRequest($uuid, $request->get('transition'));

        try {
            $this->useCase->execute($useCaseRequest);
        } catch (MerchantOnboardingNotFoundException $exception) {
            throw new NotFoundHttpException('Merchant onboarding not found', $exception);
        } catch (MerchantOnboardingStepsIncompleteException $exception) {
            throw new BadRequestHttpException('Merchant has incomplete steps', $exception);
        } catch (WorkflowException $exception) {
            throw new BadRequestHttpException('Transition not supported', $exception);
        }
    }
}
