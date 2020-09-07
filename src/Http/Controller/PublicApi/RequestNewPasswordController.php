<?php

declare(strict_types=1);

namespace App\Http\Controller\PublicApi;

use App\Application\UseCase\RequestNewPassword\RequestNewPasswordRequest;
use App\Application\UseCase\RequestNewPassword\RequestNewPasswordUseCase;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Request;

/**
 * @OA\Post(
 *     path="/merchant/user/request-new-password",
 *     operationId="request_new_password",
 *     summary="Request New Password",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Dashboard Users"},
 *     x={"groups":{"private"}},
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json",
 *          @OA\Schema(ref="#/components/schemas/RequestNewPasswordRequest"))
 *     ),
 *
 *     @OA\Response(response=200, description="New password successfully requested"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class RequestNewPasswordController
{
    private $useCase;

    public function __construct(RequestNewPasswordUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(Request $request): void
    {
        $this->useCase->execute(new RequestNewPasswordRequest($request->request->get('email')));
    }
}
