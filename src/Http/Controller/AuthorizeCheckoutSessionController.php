<?php

namespace App\Http\Controller;

use App\Application\UseCase\CreateOrder\CreateOrderUseCase;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\OrderResponse\OrderResponseFactory;
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

    private $responseFactory;

    public function __construct(
        CreateOrderUseCase $useCase,
        Security $security,
        CreateOrderRequestFactory $orderRequestFactory,
        OrderResponseFactory $responseFactory
    ) {
        $this->useCase = $useCase;
        $this->security = $security;
        $this->orderRequestFactory = $orderRequestFactory;
        $this->responseFactory = $responseFactory;
    }

    public function execute(Request $request)
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $useCaseRequest = $this->orderRequestFactory
            ->createForAuthorizeCheckoutSession($request, $user->getCheckoutSession());

        $orderContainer = $this->useCase->execute($useCaseRequest);

        if ($orderContainer->getOrder()->getState() === OrderStateManager::STATE_AUTHORIZED) {
            return new Response('', JsonResponse::HTTP_CREATED);
        }

        return new JsonResponse(
            $this->responseFactory->createAuthorizeResponse($orderContainer)->toArray(),
            JsonResponse::HTTP_BAD_REQUEST
        );
    }
}
