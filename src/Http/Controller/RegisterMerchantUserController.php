<?php

namespace App\Http\Controller;

use App\Application\UseCase\RegisterMerchantUser\RegisterMerchantUserRequest;
use App\Application\UseCase\RegisterMerchantUser\RegisterMerchantUserUseCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RegisterMerchantUserController
{
    private $useCase;

    public function __construct(RegisterMerchantUserUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(Request $request, int $merchantId): Response
    {
        $this->useCase->execute(
            new RegisterMerchantUserRequest(
                $merchantId,
                $request->request->get('email'),
                $request->request->get('password')
            )
        );

        return new Response('', JsonResponse::HTTP_CREATED);
    }
}
