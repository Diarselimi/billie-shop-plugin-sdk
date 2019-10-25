<?php

namespace App\Http\Controller\PrivateApi;

use App\Application\UseCase\RegisterMerchantUser\MerchantUserAlreadyExistsException;
use App\Application\UseCase\RegisterMerchantUser\RegisterMerchantUserRequest;
use App\Application\UseCase\RegisterMerchantUser\RegisterMerchantUserUseCase;
use App\DomainModel\Merchant\MerchantNotFoundException;
use App\DomainModel\MerchantUser\RoleNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @OA\Post(
 *     path="/merchant/{merchantId}/user",
 *     operationId="register_merchant_user",
 *     summary="Register Merchant User",
 *
 *     tags={"Dashboard Users"},
 *     x={"groups":{"private"}},
 *
 *     @OA\Parameter(in="path", name="merchantId", description="Merchant ID", @OA\Schema(type="integer"), required=true),
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json",
 *          @OA\Schema(ref="#/components/schemas/RegisterMerchantUserRequest"))
 *     ),
 *
 *     @OA\Response(response=201, description="Merchant user was successfully registered"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class RegisterMerchantUserController
{
    private $useCase;

    public function __construct(RegisterMerchantUserUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(Request $request, int $merchantId): Response
    {
        try {
            $this->useCase->execute(
                new RegisterMerchantUserRequest(
                    $merchantId,
                    $request->request->get('first_name'),
                    $request->request->get('last_name'),
                    $request->request->get('email'),
                    $request->request->get('password'),
                    $request->request->get('permissions'),
                    $request->request->get('role_uuid')
                )
            );

            return new Response('', JsonResponse::HTTP_CREATED);
        } catch (MerchantNotFoundException $exception) {
            throw new NotFoundHttpException("Merchant doesn't exist");
        } catch (RoleNotFoundException $exception) {
            throw new NotFoundHttpException("Role doesn't exist");
        } catch (MerchantUserAlreadyExistsException $exception) {
            throw new AccessDeniedHttpException("This user cannot be created");
        }
    }
}
