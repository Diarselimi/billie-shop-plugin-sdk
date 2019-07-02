<?php

namespace App\Http\Controller\PublicApi;

use App\Application\UseCase\MerchantUserLogin\MerchantUserLoginException;
use App\Application\UseCase\MerchantUserLogin\MerchantUserLoginRequest;
use App\Application\UseCase\MerchantUserLogin\MerchantUserLoginUseCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * @OA\Post(
 *     path="/merchant/user/login",
 *     operationId="merchant_user_login",
 *     summary="Merchant User Login",
 *     security={},
 *
 *     tags={"Authentication"},
 *     x={"groups":{"dashboard"}},
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json",
 *          @OA\Schema(ref="#/components/schemas/MerchantUserLoginRequest"))
 *     ),
 *
 *     @OA\Response(response=200, description="Login successful", @OA\JsonContent(ref="#/components/schemas/MerchantUserLoginResponse")),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class MerchantUserLoginController
{
    private $useCase;

    public function __construct(MerchantUserLoginUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(Request $request): JsonResponse
    {
        try {
            $response = $this->useCase->execute(
                new MerchantUserLoginRequest(
                    $request->request->get('email'),
                    $request->request->get('password')
                )
            );

            return new JsonResponse($response->toArray());
        } catch (MerchantUserLoginException $exception) {
            throw new UnauthorizedHttpException('Bearer');
        }
    }
}
