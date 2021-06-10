<?php

namespace App\Http\Controller\PublicApi\Dashboard;

use App\Application\UseCase\GetMerchantUser\GetMerchantUserRequest;
use App\Application\UseCase\GetMerchantUser\GetMerchantUserUseCase;
use App\DomainModel\Merchant\MerchantCompanyNotFoundException;
use App\DomainModel\MerchantUser\MerchantUserNotFoundException;
use App\Http\Authentication\UserProvider;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @IsGranted("ROLE_AUTHENTICATED_AS_MERCHANT_USER")
 *
 * @OA\Get(
 *     path="/merchant/user",
 *     operationId="get_merchant_user",
 *     summary="Get Merchant User",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Dashboard Users"},
 *     x={"groups":{"private"}},
 *
 *     @OA\Response(response=200, @OA\JsonContent(ref="#/components/schemas/MerchantUserDTO"), description="Merchant User details"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class GetMerchantUserController
{
    private $getMerchantUserUseCase;

    private $userProvider;

    public function __construct(GetMerchantUserUseCase $getMerchantUserUseCase, UserProvider $userProvider)
    {
        $this->getMerchantUserUseCase = $getMerchantUserUseCase;
        $this->userProvider = $userProvider;
    }

    public function execute(): JsonResponse
    {
        try {
            $response = $this->getMerchantUserUseCase->execute(
                new GetMerchantUserRequest(
                    $this->userProvider->getMerchantUser()->getUserEntity()->getUuid()
                )
            );

            return new JsonResponse($response->toArray());
        } catch (MerchantUserNotFoundException $exception) {
            throw new NotFoundHttpException('User not found');
        } catch (MerchantCompanyNotFoundException $exception) {
            throw new NotFoundHttpException('Merchant company not found');
        }
    }
}
