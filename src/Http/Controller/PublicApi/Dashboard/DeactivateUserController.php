<?php

declare(strict_types=1);

namespace App\Http\Controller\PublicApi\Dashboard;

use App\Application\UseCase\DeactivateUser\DeactivateUserException;
use App\Application\UseCase\DeactivateUser\DeactivateUserRequest;
use App\Application\UseCase\DeactivateUser\DeactivateUserUseCase;
use App\DomainModel\MerchantUser\MerchantUserNotFoundException;
use App\Http\Authentication\UserProvider;
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
 *     path="/merchant/user/{uuid}/deactivate",
 *     operationId="user_deactivate",
 *     summary="Deactivate User",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Dashboard Users"},
 *     x={"groups":{"private"}},
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json",
 *          @OA\Schema(ref="#/components/schemas/DeactivateUserRequest"))
 *     ),
 *
 *     @OA\Response(response=204, description="User successfully deactivated"),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class DeactivateUserController
{
    private $useCase;

    private $userProvider;

    public function __construct(DeactivateUserUseCase $useCase, UserProvider $userProvider)
    {
        $this->useCase = $useCase;
        $this->userProvider = $userProvider;
    }

    public function execute(Request $request, string $uuid): void
    {
        try {
            $merchantId = $request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID);
            $deactivateUserRequest = (new DeactivateUserRequest())
                ->setMerchantId($merchantId)
                ->setUserUuid($uuid)
                ->setCurrentUserUuid($this->userProvider->getMerchantUser()->getUserEntity()->getUuid());
            $this->useCase->execute($deactivateUserRequest);
        } catch (MerchantUserNotFoundException $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        } catch (DeactivateUserException $exception) {
            throw new BadRequestHttpException($exception->getMessage());
        }
    }
}
