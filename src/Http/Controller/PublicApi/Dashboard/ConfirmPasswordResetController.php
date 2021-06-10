<?php

declare(strict_types=1);

namespace App\Http\Controller\PublicApi\Dashboard;

use App\Application\UseCase\ConfirmPasswordReset\ConfirmPasswordResetRequest;
use App\Application\UseCase\ConfirmPasswordReset\ConfirmPasswordResetUseCase;
use App\Application\UseCase\ConfirmPasswordReset\ValidPasswordResetTokenNotFoundException;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * @OA\Get(
 *     path="/merchant/user/confirm-password-reset",
 *     operationId="confirm_password_reset",
 *     summary="Confirm Password Reset",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Dashboard Users"},
 *     x={"groups":{"private"}},
 *
 *     @OA\Parameter(
 *         in="query",
 *         name="token",
 *         description="Token",
 *         @OA\Schema(ref="#/components/schemas/TinyText"),
 *         required=true
 *     ),
 *
 *     @OA\Response(response=204, description="Password confirm valid"),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class ConfirmPasswordResetController
{
    private $useCase;

    public function __construct(ConfirmPasswordResetUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(Request $request): void
    {
        $useCaseRequest = (new ConfirmPasswordResetRequest($request->query->get('token')));

        try {
            $this->useCase->execute($useCaseRequest);
        } catch (ValidPasswordResetTokenNotFoundException $exception) {
            throw new UnauthorizedHttpException('token', 'Invalid token');
        }
    }
}
