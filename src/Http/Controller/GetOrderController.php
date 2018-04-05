<?php

namespace App\Http\Controller;

use App\Application\UseCase\GetOrder\GetOrderRequest;
use App\Application\UseCase\GetOrder\GetOrderUseCase;
use App\Http\HttpConstantsInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class GetOrderController
{
    private $useCase;

    public function __construct(GetOrderUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(string $externalCode, Request $request)
    {
        $request = new GetOrderRequest($externalCode, $request->headers->get(HttpConstantsInterface::REQUEST_HEADER_API_USER));
        $response = $this->useCase->execute($request);

        $json = [
            'external_code' => $response->getExternalCode(),
            'state' => $response->getState(),
            'debtor_company' => [
                'name' => $response->getCompanyName(),
                'house_number' => $response->getCompanyAddressHouseNumber(),
                'street' => $response->getCompanyAddressStreet(),
                'postal_code' => $response->getCompanyAddressPostalCode(),
                'country' => $response->getCompanyAddressCountry(),
            ],
            'bank_account' => [
                'iban' => $response->getBankAccountIban(),
                'bic' => $response->getBankAccountBic(),
            ],
            'invoice' => [
                'number' => $response->getInvoiceNumber(),
                'payout_amount' => $response->getPayoutAmount(),
                'fee_amount' => $response->getFeeAmount(),
                'fee_rate' => $response->getFeeRate(),
                'due_date' => $response->getDueDate() ? $response->getDueDate()->format('Y-m-d H:i:s') : null,
            ],
        ];

        return new JsonResponse($json);
    }
}
