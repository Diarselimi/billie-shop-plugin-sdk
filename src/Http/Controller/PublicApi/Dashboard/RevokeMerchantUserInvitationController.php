<?php

namespace App\Http\Controller\PublicApi\Dashboard;

use App\Application\UseCase\RevokeMerchantUserInvitation\RevokeMerchantUserInvitationRequest;
use App\Application\UseCase\RevokeMerchantUserInvitation\RevokeMerchantUserInvitationUseCase;
use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationNotFoundException;
use App\Http\HttpConstantsInterface;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @IsGranted("ROLE_MANAGE_USERS")
 *
 * @OA\Delete(
 *     path="/merchant/users/invitations/{uuid}",
 *     operationId="revoke_user_invitation",
 *     summary="Revoke User Invitation",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Dashboard Users"},
 *     x={"groups":{"private"}},
 *
 *     @OA\Parameter(in="path", name="uuid", @OA\Schema(ref="#/components/schemas/UUID"), required=true),
 *
 *     @OA\Response(response=204, description="Successful response"),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=403, ref="#/components/responses/Forbidden"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class RevokeMerchantUserInvitationController
{
    private $useCase;

    public function __construct(RevokeMerchantUserInvitationUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(string $uuid, Request $request): void
    {
        try {
            $useCaseRequest = (new RevokeMerchantUserInvitationRequest(
                $request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID),
                $uuid
            ));

            $this->useCase->execute($useCaseRequest);
        } catch (MerchantUserInvitationNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        }
    }
}
