<?php

declare(strict_types=1);

namespace App\Http\Controller\PublicApi\Dashboard;

use App\Application\UseCase\UpdateInvitationRole\UpdateInvitationRoleException;
use App\Application\UseCase\UpdateInvitationRole\UpdateInvitationRoleRequest;
use App\Application\UseCase\UpdateInvitationRole\UpdateInvitationRoleUseCase;
use App\DomainModel\MerchantUser\RoleNotFoundException;
use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationNotFoundException;
use App\Http\HttpConstantsInterface;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @IsGranted("ROLE_MANAGE_USERS")
 *
 * @OA\Post(
 *     path="/merchant/users/invitations/role",
 *     operationId="role_invitation_update",
 *     summary="Update Invitation Role",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Dashboard Users"},
 *     x={"groups":{"private"}},
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json",
 *          @OA\Schema(ref="#/components/schemas/UpdateInvitationRoleRequest"))
 *     ),
 *
 *     @OA\Response(response=204, description="Role successfully updated"),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class UpdateInvitationRoleController
{
    private $useCase;

    public function __construct(UpdateInvitationRoleUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(Request $request): void
    {
        try {
            $merchantId = $request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID);
            $updateRoleRequest = (new UpdateInvitationRoleRequest())
                ->setMerchantId($merchantId)
                ->setEmail($request->request->get('email'))
                ->setRoleUuid($request->request->get('role_uuid'));
            $this->useCase->execute($updateRoleRequest);
        } catch (MerchantUserInvitationNotFoundException | RoleNotFoundException $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        } catch (UpdateInvitationRoleException $exception) {
            throw new BadRequestHttpException($exception->getMessage());
        }
    }
}
