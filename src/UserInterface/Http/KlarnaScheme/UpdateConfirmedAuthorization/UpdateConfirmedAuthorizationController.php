<?php

namespace App\UserInterface\Http\KlarnaScheme\UpdateConfirmedAuthorization;

use App\Application\CommandBus;
use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\RequestValidationException;
use App\Application\UseCase\UpdateOrder\UpdateOrderRequest;
use App\UserInterface\Http\KlarnaScheme\KlarnaResponse;
use Ozean12\Money\Money;
use Ozean12\Money\TaxedMoney\TaxedMoney;
use Symfony\Component\HttpFoundation\Request;

class UpdateConfirmedAuthorizationController
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
        } catch (RequestValidationException $ex) {
            return KlarnaResponse::withErrorMessage('Authorization could not be adjusted');
        }

        return new KlarnaResponse(['result' => 'accepted']);
    }

    private function isRequestInvalid(Request $request): bool
    {
        $amount = $request->request->get('amount');
        $taxAmount = $request->request->get('tax_amount');

        return $amount === null || $taxAmount === null;
    }

    private function process(Request $request): void
    {
        $gross = new Money($request->get('amount'), 2);
        $tax = new Money($request->get('tax_amount'), 2);
        $net = $gross->subtract($tax);
        $amount = new TaxedMoney($gross, $net, $tax);

        $command = new UpdateOrderRequest($request->get('orderId'), null, null, $amount);

        $this->bus->process($command);
    }
}
