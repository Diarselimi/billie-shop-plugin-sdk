<?php

namespace App\Http\Controller\PublicApi;

use App\Application\UseCase\GetMerchantUser\GetMerchantUserRequest;
use App\Application\UseCase\GetMerchantUser\GetMerchantUserUseCase;
use App\Application\UseCase\GetMerchantUser\MerchantUserNotFoundException;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Security;

/**
 * @OA\Get(
 *     path="/merchant/user",
 *     operationId="get_merchant_user",
 *     summary="Get Merchant User",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Dashboard Users"},
 *     x={"groups":{"private"}},
 *
 *     @OA\Response(response=200, @OA\JsonContent(ref="#/components/schemas/GetMerchantUserResponse"), description="Merchant User details"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class GetMerchantUserController
{
    private $getMerchantUserUseCase;

    private $security;

    public function __construct(GetMerchantUserUseCase $getMerchantUserUseCase, Security $security)
    {
        $this->getMerchantUserUseCase = $getMerchantUserUseCase;
        $this->security = $security;
    }

    public function execute(): JsonResponse
    {
        try {
            $response = $this->getMerchantUserUseCase->execute(
                new GetMerchantUserRequest(
                    $this->security->getUser()->getUsername()
                )
            );

            return new JsonResponse($response->toArray());
        } catch (MerchantUserNotFoundException $exception) {
            throw new NotFoundHttpException();
        }
    }
}
