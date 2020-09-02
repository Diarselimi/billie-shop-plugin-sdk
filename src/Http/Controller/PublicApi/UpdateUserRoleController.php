<?php

declare(strict_types=1);

namespace App\Http\Controller\PublicApi;

use App\Application\UseCase\UpdateUserRole\UpdateUserRoleException;
use App\Application\UseCase\UpdateUserRole\UpdateUserRoleRequest;
use App\Application\UseCase\UpdateUserRole\UpdateUserRoleUseCase;
use App\DomainModel\MerchantUser\MerchantUserNotFoundException;
use App\DomainModel\MerchantUser\RoleNotFoundException;
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
 *     path="/merchant/user/role",
 *     operationId="role_user_update",
 *     summary="Update User Role",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Dashboard Users"},
 *     x={"groups":{"private"}},
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json",
 *          @OA\Schema(ref="#/components/schemas/UpdateUserRoleRequest"))
 *     ),
 *
 *     @OA\Response(response=204, description="Role successfully updated"),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class UpdateUserRoleController
{
    private $useCase;

    public function __construct(UpdateUserRoleUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(Request $request): void
    {
        try {
            $merchantId = $request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID);
            $updateRoleRequest = (new UpdateUserRoleRequest())
                ->setMerchantId($merchantId)
                ->setUserUuid($request->request->get('user_uuid'))
                ->setRoleUuid($request->request->get('role_uuid'));
            $this->useCase->execute($updateRoleRequest);
        } catch (MerchantUserNotFoundException | RoleNotFoundException $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        } catch (UpdateUserRoleException $exception) {
            throw new BadRequestHttpException($exception->getMessage());
        }
    }
}
