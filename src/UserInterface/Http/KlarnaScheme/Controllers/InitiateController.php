<?php

namespace App\UserInterface\Http\KlarnaScheme\Controllers;

use App\Application\CommandBus;
use App\Application\UseCase\InitiateCheckoutSession\InitiateCheckoutSession;
use App\DomainModel\CheckoutSession\CountryNotSupported;
use App\Infrastructure\UuidGeneration\UuidGenerator;
use Symfony\Component\HttpFoundation\JsonResponse;
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

    public function execute(Request $request): JsonResponse
    {
        if ($this->isRequestInvalid($request)) {
            return new JsonResponse([
                'error_messages' => ['Invalid request'],
            ]);
        }

        if ($this->doesNotSupportCustomerType($request)) {
            return new JsonResponse([
                'error_messages' => ['Customer type not supported'],
            ]);
        }

        try {
            $command = new InitiateCheckoutSession(
                $this->uuidGenerator->generate(),
                $request->request->get('country'),
                1 // TODO
            );
        } catch (CountryNotSupported $exception) {
            return new JsonResponse([
                'error_messages' => [$exception->getMessage()],
            ]);
        }

        $this->bus->process($command);

        return new JsonResponse([
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
