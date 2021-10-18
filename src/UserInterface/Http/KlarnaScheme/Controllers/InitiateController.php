<?php

namespace App\UserInterface\Http\KlarnaScheme\Controllers;

use App\Application\CommandBus;
use App\Application\UseCase\InitiateCheckoutSession\InitiateCheckoutSession;
use App\DomainModel\CheckoutSession\CountryNotSupported;
use App\Infrastructure\UuidGeneration\UuidGenerator;
use App\UserInterface\Http\KlarnaScheme\KlarnaResponse;
use Symfony\Component\HttpFoundation\Request;

class InitiateController
{
    private const SUPPORTED_CUSTOMER_TYPE = 'organization';

    private CommandBus $bus;

    private UuidGenerator $uuidGenerator;

    public function __construct(
        CommandBus $bus,
        UuidGenerator $uuidGenerator
    ) {
        $this->bus = $bus;
        $this->uuidGenerator = $uuidGenerator;
    }

    public function execute(Request $request): KlarnaResponse
    {
        if ($this->isRequestInvalid($request)) {
            return KlarnaResponse::withErrorMessage('Invalid request');
        }

        if ($this->doesNotSupportCustomerType($request)) {
            return KlarnaResponse::withErrorMessage('Customer type not supported');
        }

        try {
            $command = new InitiateCheckoutSession(
                $this->uuidGenerator->generate(),
                $request->request->get('country'),
                1, // TODO
                null
            );
        } catch (CountryNotSupported $ex) {
            return KlarnaResponse::withErrorFromException($ex);
        }

        $this->bus->process($command);

        return new KlarnaResponse([
            'payment_method_session_id' => (string) $command->token(),
        ]);
    }

    private function isRequestInvalid(Request $request): bool
    {
        $country = $request->request->get('country');
        $customerType = $request->request->get('customer')['type'] ?? null;

        return $country === null || $customerType === null;
    }

    private function doesNotSupportCustomerType(Request $request): bool
    {
        return $request->request->get('customer')['type'] !== self::SUPPORTED_CUSTOMER_TYPE;
    }
}
