<?php

declare(strict_types=1);

namespace App\Http\Controller\PublicApi\Dashboard;

use App\Application\UseCase\ResetPassword\ResetPasswordException;
use App\Application\UseCase\ResetPassword\ResetPasswordRequest;
use App\Application\UseCase\ResetPassword\ResetPasswordUseCase;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @OA\Post(
 *     path="/merchant/user/reset-password",
 *     operationId="reset_password",
 *     summary="Reset Password",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Dashboard Users"},
 *     x={"groups":{"private"}},
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json",
 *          @OA\Schema(ref="#/components/schemas/ResetPasswordRequest"))
 *     ),
 *
 *     @OA\Response(response=204, description="Password successfully reset"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class ResetPasswordController
{
    private $useCase;

    public function __construct(ResetPasswordUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(Request $request): void
    {
        try {
            $this->useCase->execute(new ResetPasswordRequest(
                $request->request->get('password'),
                $request->request->get('token')
            ));
        } catch (ResetPasswordException $exception) {
            throw new HttpException(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $exception->getMessage(),
                $exception
            );
        }
    }
}
