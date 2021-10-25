<?php

namespace App\UserInterface\Http\KlarnaScheme\Authorize;

use App\Application\CommandBus;
use App\DomainModel\CheckoutSession\CheckoutSession;
use App\DomainModel\CheckoutSession\CheckoutSessionRepository;
use App\DomainModel\CheckoutSession\Token;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\OrderResponse\LegacyOrderResponseFactory;
use App\Http\RequestTransformer\CreateOrder\AuthorizeOrderCommandFactory;
use App\UserInterface\Http\KlarnaScheme\KlarnaResponse;
use Symfony\Component\HttpFoundation\Request;

class AuthorizeController
{
    private CommandBus $bus;

    private AuthorizeOrderCommandFactory $commandFactory;

    private CheckoutSessionRepository $checkoutSessionRepository;

    private OrderContainerFactory $orderContainerFactory;

    private LegacyOrderResponseFactory $responseFactory;

    private int $defaultOrderDuration;

    public function __construct(
        CommandBus $bus,
        AuthorizeOrderCommandFactory $commandFactory,
        CheckoutSessionRepository $checkoutSessionRepository,
        OrderContainerFactory $orderContainerFactory,
        LegacyOrderResponseFactory $responseFactory,
        int $defaultOrderDuration
    ) {
        $this->bus = $bus;
        $this->commandFactory = $commandFactory;
        $this->checkoutSessionRepository = $checkoutSessionRepository;
        $this->orderContainerFactory = $orderContainerFactory;
        $this->responseFactory = $responseFactory;
        $this->defaultOrderDuration = $defaultOrderDuration;
    }

    public function execute(Request $request): KlarnaResponse
    {
        if ($this->isRequestInvalid($request)) {
            return KlarnaResponse::withErrorMessage('Invalid request'); //TODO: throw missing fields error
        }

        if (null === $checkoutSession = $this->loadSession($request)) {
            return KlarnaResponse::withErrorMessage('Payment session not found');
        }

        if ($this->isPreAuthorization($request)) {
            return $this->performPreAuthorization($request);
        }

        return $this->performAuthorization($request, $checkoutSession);
    }

    private function isRequestInvalid(Request $request): bool
    {
        return empty($request->request->get('payment_method')['payment_method_session_id']);
    }

    private function isPreAuthorization(Request $request): bool
    {
        return empty($request->request->get('payment_method')['ui'])
            || empty($request->request->get('payment_method')['ui']['data'])
        ;
    }

    private function loadSession(Request $request): ?CheckoutSession
    {
        return $this->checkoutSessionRepository->findByToken(
            Token::fromHash($request->request->get('payment_method')['payment_method_session_id'])
        );
    }

    private function performPreAuthorization(Request $request): KlarnaResponse
    {
        return new PreAuthorizeResponse($request->request->all(), $this->defaultOrderDuration);
    }

    private function performAuthorization(Request $request, CheckoutSession $checkoutSession): KlarnaResponse
    {
        $command = $this->commandFactory->create(
            $request->request->get('payment_method')['ui']['data'],
            $checkoutSession,
            OrderEntity::CREATION_SOURCE_CHECKOUT
        );

        $this->bus->process($command);

        $orderContainer = $this->orderContainerFactory->getCachedOrderContainer();
        $orderResponse = $this->responseFactory->createAuthorizeResponse($orderContainer);

        return new AuthorizeResponse($orderContainer, $orderResponse);
    }
}
