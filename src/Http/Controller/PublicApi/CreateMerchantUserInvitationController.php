<?php

declare(strict_types=1);

namespace App\Http\Controller\PublicApi;

use App\Application\UseCase\CreateMerchantUserInvitation\CreateMerchantUserInvitationRequest;
use App\Application\UseCase\CreateMerchantUserInvitation\CreateMerchantUserInvitationResponse;
use App\Application\UseCase\CreateMerchantUserInvitation\CreateMerchantUserInvitationUseCase;
use App\DomainModel\Merchant\MerchantNotFoundException;
use App\DomainModel\MerchantUser\RoleNotFoundException;
use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationAlreadyExistsException;
use App\Http\HttpConstantsInterface;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @IsGranted("ROLE_MANAGE_USERS")
 *
 * @OA\Post(
 *     path="/merchant/users/invitations",
 *     operationId="invite_merchant_user",
 *     summary="Invite Merchant User",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Dashboard Users"},
 *     x={"groups":{"private"}},
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json", @OA\Schema(
 *              ref="#/components/schemas/CreateMerchantUserInvitationRequest"
 *          ))
 *     ),
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
class CreateMerchantUserInvitationController
{
    private CreateMerchantUserInvitationUseCase $useCase;

    public function __construct(CreateMerchantUserInvitationUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(Request $request): CreateMerchantUserInvitationResponse
    {
        try {
            $useCaseRequest = (new CreateMerchantUserInvitationRequest(
                $request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID),
                $request->get('email'),
                $request->get('role_uuid')
            ));

            return $this->useCase->execute($useCaseRequest);
        } catch (RoleNotFoundException $e) {
            throw new NotFoundHttpException('Role not found', $e);
        } catch (MerchantNotFoundException $e) {
            throw new NotFoundHttpException('Merchant not found', $e);
        } catch (MerchantUserInvitationAlreadyExistsException $e) {
            throw new ConflictHttpException('Invitation for the same email already exists', $e);
        }
    }
}
