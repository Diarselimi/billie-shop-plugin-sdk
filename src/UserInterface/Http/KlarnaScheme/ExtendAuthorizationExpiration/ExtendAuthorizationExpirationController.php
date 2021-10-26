<?php

namespace App\UserInterface\Http\KlarnaScheme\ExtendAuthorizationExpiration;

use App\Application\CommandBus;
use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\ExtendOrderExpiration\ExtendOrderExpiration;
use App\UserInterface\Http\KlarnaScheme\KlarnaResponse;
use Symfony\Component\HttpFoundation\Request;

class ExtendAuthorizationExpirationController
{
    private CommandBus $bus;

    public function __construct(CommandBus $bus)
    {
        $this->bus = $bus;
    }

    public function execute(Request $request): KlarnaResponse
    {
        if ($this->isRequestInvalid($request)) {
            return KlarnaResponse::withErrorMessage('Invalid request');
        }

        try {
            $this->process($request);
        } catch (OrderNotFoundException $ex) {
            return KlarnaResponse::withErrorMessage('Authorization not found');
        } catch (\InvalidArgumentException $ex) {
            return KlarnaResponse::withErrorMessage('Expiration could not be extended');
        }

        return new KlarnaResponse(['result' => 'accepted']);
    }

    private function isRequestInvalid(Request $request): bool
    {
        return null === $request->get('expires_at');
    }

    private function process(Request $request): void
    {
        $command = new ExtendOrderExpiration(
            $request->get('orderId'),
            new \DateTimeImmutable($request->get('expires_at')),
        );

        $this->bus->process($command);
    }
}
