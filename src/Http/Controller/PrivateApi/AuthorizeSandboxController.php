<?php

declare(strict_types=1);

namespace App\Http\Controller\PrivateApi;

use App\Application\UseCase\AuthorizeSandbox\AuthorizeSandboxDTO;
use App\Application\UseCase\AuthorizeSandbox\AuthorizeSandboxUseCase;
use App\Http\HttpConstantsInterface;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * @OA\Post(
 *     path="/authorize-sandbox",
 *     operationId="authorize-sandbox",
 *     summary="Authorize sandbox",
 *     description="Authorize production users for a sandbox",
 *
 *     tags={"Dashboard Users"},
 *     x={"groups":{"private"}},
 *
 *     @OA\Response(
 *          response=200,
 *          description="Successful response",
 *          @OA\JsonContent(ref="#/components/schemas/AuthorizeSandboxDTO")
 *     ),
 *
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class AuthorizeSandboxController
{
    private $authorizeSandboxUseCase;

    public function __construct(
        AuthorizeSandboxUseCase $authorizeSandboxUseCase
    ) {
        $this->authorizeSandboxUseCase = $authorizeSandboxUseCase;
    }

    public function execute(Request $request): AuthorizeSandboxDTO
    {
        $token = $request->headers->get(HttpConstantsInterface::REQUEST_HEADER_AUTHORIZATION);
        if ($token === null) {
            throw new AccessDeniedHttpException('User can not be authenticated');
        }

        try {
            return $this->authorizeSandboxUseCase->execute($token);
        } catch (AuthenticationException $exception) {
            throw new AccessDeniedHttpException($exception->getMessage());
        }
    }
}
