<?php

namespace App\Http\Controller\PublicApi;

use App\Application\UseCase\GetInvitedMerchantUser\GetInvitedMerchantUserRequest;
use App\Application\UseCase\GetInvitedMerchantUser\GetInvitedMerchantUserResponse;
use App\Application\UseCase\GetInvitedMerchantUser\GetInvitedMerchantUserUseCase;
use App\Http\Authentication\UserProvider;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @IsGranted({"ROLE_AUTHENTICATED_AS_INVITED_USER"})
 *
 * @OA\Get(
 *     path="/merchant/users/invitations/{token}/details",
 *     operationId="get_invited_user_details",
 *     summary="Get Invited User",
 *
 *     tags={"Dashboard Users"},
 *     x={"groups":{"private"}},
 *
 *     @OA\Parameter(in="path", description="Invitation Token", name="token", @OA\Schema(type="string", maxLength=36, example="689e71e69zk8484k0k0cckkgs08cogk4w0ko"), required=true),
 *
 *     @OA\Response(
 *          response=200,
 *          description="Successful response",
 *          @OA\JsonContent(ref="#/components/schemas/GetInvitedMerchantUserResponse")
 *     ),
 *
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class GetInvitedMerchantUserController
{
    private $useCase;

    private $userProvider;

    public function __construct(GetInvitedMerchantUserUseCase $useCase, UserProvider $userProvider)
    {
        $this->useCase = $useCase;
        $this->userProvider = $userProvider;
    }

    public function execute(): GetInvitedMerchantUserResponse
    {
        $useCaseRequest = new GetInvitedMerchantUserRequest(
            $this->userProvider->getInvitedUser()->getInvitation()
        );

        return $this->useCase->execute($useCaseRequest);
    }
}
