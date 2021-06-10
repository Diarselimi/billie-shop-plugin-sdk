<?php

namespace App\Http\Controller\PublicApi\Dashboard;

use App\Application\UseCase\MerchantUserLogin\MerchantUserLoginResponse;
use App\Application\UseCase\RegisterInvitedMerchantUser\Exception\RegisterInvitedMerchantUserException;
use App\Application\UseCase\RegisterInvitedMerchantUser\RegisterInvitedMerchantUserRequest;
use App\Application\UseCase\RegisterInvitedMerchantUser\RegisterInvitedMerchantUserUseCase;
use App\Http\Authentication\UserProvider;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/**
 * @IsGranted({"ROLE_AUTHENTICATED_AS_INVITED_USER"})
 *
 * @OA\Post(
 *     path="/merchant/users/invitations/{token}/signup",
 *     operationId="register_invited_user",
 *     summary="Register Invited User",
 *
 *     tags={"Dashboard Users"},
 *     x={"groups":{"private"}},
 *
 *     @OA\Parameter(in="path", description="Invitation Token", name="token", @OA\Schema(type="string", maxLength=36, example="689e71e69zk8484k0k0cckkgs08cogk4w0ko"), required=true),
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/RegisterInvitedMerchantUserRequest"))
 *     ),
 *
 *     @OA\Response(
 *          response=200,
 *          description="Successful response",
 *          @OA\JsonContent(ref="#/components/schemas/MerchantUserLoginResponse")
 *     ),
 *
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class RegisterInvitedMerchantUserController
{
    private $useCase;

    private $userProvider;

    public function __construct(RegisterInvitedMerchantUserUseCase $useCase, UserProvider $userProvider)
    {
        $this->useCase = $useCase;
        $this->userProvider = $userProvider;
    }

    public function execute(Request $request): MerchantUserLoginResponse
    {
        $useCaseRequest = new RegisterInvitedMerchantUserRequest(
            $this->userProvider->getInvitedUser()->getInvitation(),
            $request->request->get('first_name'),
            $request->request->get('last_name'),
            $request->request->get('password'),
            $request->request->get('tc_accepted')
        );

        try {
            return $this->useCase->execute($useCaseRequest);
        } catch (RegisterInvitedMerchantUserException $exception) {
            throw new ConflictHttpException('Merchant user with the same login already exists', $exception);
        }
    }
}
