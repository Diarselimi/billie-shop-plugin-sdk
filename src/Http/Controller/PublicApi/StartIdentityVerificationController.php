<?php

declare(strict_types=1);

namespace App\Http\Controller\PublicApi;

use App\Application\Exception\MerchantOnboardingStepTransitionException;
use App\Application\UseCase\StartIdentityVerification\StartIdentityVerificationException;
use App\Application\UseCase\StartIdentityVerification\StartIdentityVerificationRequest;
use App\Application\UseCase\StartIdentityVerification\StartIdentityVerificationUseCase;
use App\Application\UseCase\StartIdentityVerificationResponse;
use App\Http\Authentication\UserProvider;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @IsGranted({"ROLE_MANAGE_ONBOARDING"})
 * @OA\Post(
 *     path="/merchant/user/start-identity-verification",
 *     operationId="start_identity_verification",
 *     summary="Start Identity Verification (KYC)",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Dashboard Users"},
 *     x={"groups":{"private"}},
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/StartIdentityVerificationRedirects"))
 *     ),
 *
 *     @OA\Response(
 *          response=200,
 *          description="Successful response",
 *          @OA\JsonContent(ref="#/components/schemas/StartIdentityVerificationResponse")
 *     ),
 *
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=403, ref="#/components/responses/Forbidden"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class StartIdentityVerificationController
{
    private $useCase;

    private $userProvider;

    public function __construct(StartIdentityVerificationUseCase $useCase, UserProvider $userProvider)
    {
        $this->useCase = $useCase;
        $this->userProvider = $userProvider;
    }

    public function execute(Request $request): StartIdentityVerificationResponse
    {
        $user = $this->userProvider->getMerchantUser()->getUserEntity();
        $merchant = $this->userProvider->getMerchantUser()->getMerchant();

        $useCaseRequest = (new StartIdentityVerificationRequest($merchant->getId(), $merchant->getPaymentUuid()))
            ->setMerchantUserId($user->getId())
            ->setEmail($this->userProvider->getMerchantUser()->getEmail())
            ->setFirstName($user->getFirstName())
            ->setLastName($user->getLastName())
            ->setRedirectUrlCouponRequested($request->get('redirect_url_coupon_requested'))
            ->setRedirectUrlDeclined($request->get('redirect_url_declined'))
            ->setRedirectUrlReviewPending($request->get('redirect_url_review_pending'));

        if ($user->getSignatoryPowerUuid()) {
            $useCaseRequest->setSignatoryPowerUuid($user->getSignatoryPowerUuid());
        }

        try {
            return $this->useCase->execute($useCaseRequest);
        } catch (StartIdentityVerificationException $exception) {
            throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, "Identity verification can't be started", $exception);
        } catch (MerchantOnboardingStepTransitionException $exception) {
            throw new AccessDeniedHttpException($exception->getMessage());
        }
    }
}
