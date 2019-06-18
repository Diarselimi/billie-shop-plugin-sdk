<?php

namespace App\Http\Controller;

use App\Application\UseCase\MerchantUserLogout\MerchantUserLogoutException;
use App\Application\UseCase\MerchantUserLogout\MerchantUserLogoutRequest;
use App\Application\UseCase\MerchantUserLogout\MerchantUserLogoutUseCase;
use App\Http\HttpConstantsInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * @OA\Post(
 *     path="/merchant/user/logout",
 *     operationId="merchant_user_logout",
 *     summary="Merchant User Logout",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Dashboard API"},
 *     x={"groups":{"public"}},
 *
 *     @OA\Response(response=200, description="Logout successful"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class MerchantUserLogoutController
{
    private $useCase;

    public function __construct(MerchantUserLogoutUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(Request $request): Response
    {
        try {
            $this->useCase->execute(
                new MerchantUserLogoutRequest($request->headers->get(HttpConstantsInterface::REQUEST_HEADER_AUTHORIZATION))
            );
        } catch (MerchantUserLogoutException $exception) {
            throw new UnauthorizedHttpException('Bearer');
        }

        return new Response();
    }
}
