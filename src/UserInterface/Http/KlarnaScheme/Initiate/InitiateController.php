<?php

namespace App\UserInterface\Http\KlarnaScheme\Initiate;

use App\Application\CommandBus;
use App\Application\UseCase\InitiateCheckoutSession\InitiateCheckoutSession;
use App\Application\UseCase\InitiateCheckoutSession\MerchantNotFound;
use App\DomainModel\CheckoutSession\ContextNotSupported;
use App\DomainModel\CheckoutSession\Token;
use App\Infrastructure\UuidGeneration\UuidGenerator;
use App\UserInterface\Http\KlarnaScheme\KlarnaResponse;
use Symfony\Component\HttpFoundation\Request;

class InitiateController
{
    private const SUPPORTED_CUSTOMER_TYPE = 'organization';

    private const SUPPORTED_INTENT = 'buy';

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

        if ($this->doesNotSupportIntent($request)) {
            return KlarnaResponse::withErrorMessage('Intent not supported');
        }

        try {
            $token = $this->process($request);
        } catch (ContextNotSupported | MerchantNotFound $ex) {
            return KlarnaResponse::withErrorFromException($ex);
        }

        return new KlarnaResponse([
            'payment_method_session_id' => (string) $token,
        ]);
    }

    private function isRequestInvalid(Request $request): bool
    {
        $country = $request->request->get('country');
        $currency = $request->request->get('currency');
        $intent = $request->request->get('intent');
        $customerType = $request->request->get('customer')['type'] ?? null;
        $merchantId = $request->request->get('merchant')['acquirer_merchant_id'] ?? null;

        return $country === null
            || $currency === null
            || $intent === null
            || $customerType === null
            || $merchantId === null;
    }

    private function doesNotSupportCustomerType(Request $request): bool
    {
        return $request->request->get('customer')['type'] !== self::SUPPORTED_CUSTOMER_TYPE;
    }

    private function doesNotSupportIntent(Request $request): bool
    {
        return $request->request->get('intent') !== self::SUPPORTED_INTENT;
    }

    private function process(Request $request): Token
    {
        $command = InitiateCheckoutSession::forKlarna(
            $this->uuidGenerator->generate(),
            $request->request->get('country'),
            $request->request->get('currency'),
            $request->request->get('merchant')['acquirer_merchant_id']
        );

        $this->bus->process($command);

        return $command->token();
    }
}
