<?php

declare(strict_types=1);

namespace App\Http\Controller\PublicApi;

use App\Application\UseCase\GetMerchantUserIdentityVerification\GetMerchantUserIdentityVerificationUseCaseException;
use App\Application\UseCase\GetMerchantUserIdentityVerification\IdentityVerificationCaseNotFoundException;
use App\Application\UseCase\GetMerchantUserIdentityVerification\GetMerchantUserIdentityVerificationUseCase;
use App\DomainModel\IdentityVerification\IdentityVerificationCaseDTO;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @IsGranted({"ROLE_MANAGE_ONBOARDING"})
 * @OA\Get(
 *     path="/merchant/user/identity-verification",
 *     operationId="merchant_user_identity_verification",
 *     summary="Get Merchant User Identity Verification",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Dashboard Users"},
 *     x={"groups":{"private"}},
 *
 *     @OA\Response(
 *          response=200,
 *          description="Successful response",
 *          @OA\JsonContent(ref="#/components/schemas/MerchantUserIdentityVerificationResponse")
 *     ),
 *
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=403, ref="#/components/responses/Forbidden"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class GetMerchantUserIdentityVerificationController
{
    private $useCase;

    public function __construct(GetMerchantUserIdentityVerificationUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(): IdentityVerificationCaseDTO
    {
        try {
            return $this->useCase->execute();
        } catch (GetMerchantUserIdentityVerificationUseCaseException $exception) {
            throw new BadRequestHttpException("Identity verification can't be retrieved");
        } catch (IdentityVerificationCaseNotFoundException $exception) {
            throw new NotFoundHttpException('There is no case linked to this user');
        }
    }
}
