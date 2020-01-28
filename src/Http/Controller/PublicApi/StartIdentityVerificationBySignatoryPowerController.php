<?php

declare(strict_types=1);

namespace App\Http\Controller\PublicApi;

use App\Application\UseCase\StartIdentityVerificationBySignatoryPower\SignatoryPowerAlreadyIdentifiedException;
use App\Application\UseCase\StartIdentityVerificationBySignatoryPower\StartIdentityVerificationBySignatoryPowerException;
use App\Application\UseCase\StartIdentityVerificationBySignatoryPower\StartIdentityVerificationBySignatoryPowerRequest;
use App\Application\UseCase\StartIdentityVerificationBySignatoryPower\StartIdentityVerificationBySignatoryPowerUseCase;
use App\Application\UseCase\StartIdentityVerificationResponse;
use App\Http\Authentication\UserProvider;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * @IsGranted({"ROLE_AUTHENTICATED_AS_SIGNATORY_POWER_TOKEN_USER"})
 * @OA\Post(
 *     path="/merchant/signatory-powers/{token}/start-identity-verification",
 *     operationId="start_external_identity_verification",
 *     summary="Start Identity Verification By Signatory Power",
 *
 *     tags={"Dashboard Merchants"},
 *     x={"groups":{"private"}},
 *
 *     @OA\Parameter(in="path", name="token", @OA\Schema(type="string"), required=true),
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
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class StartIdentityVerificationBySignatoryPowerController
{
    private $useCase;

    private $userProvider;

    public function __construct(StartIdentityVerificationBySignatoryPowerUseCase $useCase, UserProvider $userProvider)
    {
        $this->useCase = $useCase;
        $this->userProvider = $userProvider;
    }

    public function execute(Request $request): StartIdentityVerificationResponse
    {
        $user = $this->userProvider->getSignatoryPowerTokenUser();

        try {
            $useCaseRequest = (new StartIdentityVerificationBySignatoryPowerRequest())
                ->setMerchantId($this->userProvider->getUser()->getMerchant()->getId())
                ->setSignatoryPowerDTO($user->getSignatoryPowerDTO())
                ->setRedirectUrlCouponRequested($request->get('redirect_url_coupon_requested'))
                ->setRedirectUrlDeclined($request->get('redirect_url_declined'))
                ->setRedirectUrlReviewPending($request->get('redirect_url_review_pending'))
            ;

            return $this->useCase->execute($useCaseRequest);
        } catch (StartIdentityVerificationBySignatoryPowerException $e) {
            throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, "Identity verification can't be started", $e);
        } catch (SignatoryPowerAlreadyIdentifiedException $e) {
            throw new UnauthorizedHttpException('token', $e->getMessage(), $e);
        }
    }
}
