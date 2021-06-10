<?php

namespace App\Http\Controller\PublicApi\Dashboard;

use App\Application\UseCase\CreateMerchantUserInvitation\CreateMerchantUserInvitationResponse;
use App\Application\UseCase\ResendMerchantUserInvitation\ResendMerchantUserInvitationRequest;
use App\Application\UseCase\ResendMerchantUserInvitation\ResendMerchantUserInvitationUseCase;
use App\Application\UseCase\ResendMerchantUserInvitation\ResendNotAllowedException;
use App\DomainModel\Merchant\MerchantNotFoundException;
use App\DomainModel\MerchantUser\RoleNotFoundException;
use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationNotFoundException;
use App\Http\HttpConstantsInterface;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @IsGranted("ROLE_MANAGE_USERS")
 *
 * @OA\Post(
 *     path="/merchant/users/invitations/{uuid}/resend",
 *     operationId="resend_user_invitation",
 *     summary="Resend User Invitation",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Dashboard Users"},
 *     x={"groups":{"private"}},
 *
 *     @OA\Parameter(in="path", name="uuid", @OA\Schema(ref="#/components/schemas/UUID"), required=true),
 *
 *     @OA\Response(
 *          response=200,
 *          description="Successful response",
 *          @OA\JsonContent(ref="#/components/schemas/CreateMerchantUserInvitationResponse")
 *     ),
 *
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=403, ref="#/components/responses/Forbidden"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=409, ref="#/components/responses/ResourceConflict"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class ResendMerchantUserInvitationController
{
    private $useCase;

    public function __construct(ResendMerchantUserInvitationUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(string $uuid, Request $request): CreateMerchantUserInvitationResponse
    {
        try {
            $useCaseRequest = (new ResendMerchantUserInvitationRequest(
                $request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID),
                $uuid
            ));

            return $this->useCase->execute($useCaseRequest);
        } catch (MerchantUserInvitationNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        } catch (ResendNotAllowedException $e) {
            throw new AccessDeniedHttpException(null, $e);
        } catch (MerchantNotFoundException | RoleNotFoundException $exception) {
            throw new HttpException(500, $exception->getMessage());
        }
    }
}
