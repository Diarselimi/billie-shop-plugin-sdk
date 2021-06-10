<?php

namespace App\Http\Controller\PublicApi\Dashboard;

use App\Application\UseCase\GetMerchantOnboarding\GetMerchantOnboardingRequest;
use App\Application\UseCase\GetMerchantOnboarding\GetMerchantOnboardingResponse;
use App\Application\UseCase\GetMerchantOnboarding\GetMerchantOnboardingUseCase;
use App\Http\HttpConstantsInterface;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;

/**
 * @IsGranted("ROLE_VIEW_ONBOARDING")
 * @OA\Get(
 *     path="/merchant/onboarding",
 *     operationId="get_merchant_onboarding",
 *     summary="Get Merchant Onboarding",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Dashboard Merchants"},
 *     x={"groups":{"private"}},
 *
 *     @OA\Response(
 *          response=200,
 *          description="Successful response",
 *          @OA\JsonContent(ref="#/components/schemas/GetMerchantOnboardingResponse")
 *     ),
 *
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=403, ref="#/components/responses/Forbidden"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class GetMerchantOnboardingController
{
    private $useCase;

    public function __construct(GetMerchantOnboardingUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(Request $request): GetMerchantOnboardingResponse
    {
        $useCaseRequest = new GetMerchantOnboardingRequest(
            $request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID)
        );

        return $this->useCase->execute($useCaseRequest);
    }
}
