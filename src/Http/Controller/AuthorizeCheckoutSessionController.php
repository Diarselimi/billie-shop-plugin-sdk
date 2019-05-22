<?php

namespace App\Http\Controller;

use App\Application\UseCase\CreateOrder\CreateOrderUseCase;
use App\Http\RequestHandler\CreateOrderRequestFactory;
use App\Http\Authentication\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;

class AuthorizeCheckoutSessionController
{
    private $useCase;

    private $security;

    private $orderRequestFactory;

    public function __construct(
        CreateOrderUseCase $useCase,
        Security $security,
        CreateOrderRequestFactory $orderRequestFactory
    ) {
        $this->useCase = $useCase;
        $this->security = $security;
        $this->orderRequestFactory = $orderRequestFactory;
    }

    public function execute(Request $request)
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $useCaseRequest = $this->orderRequestFactory
            ->createForAuthorizeCheckoutSession($request, $user->getCheckoutSession());

        $this->useCase->execute($useCaseRequest);

        return new Response("", JsonResponse::HTTP_CREATED);
    }
}
