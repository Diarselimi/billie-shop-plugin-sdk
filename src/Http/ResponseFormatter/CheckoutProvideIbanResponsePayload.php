<?php

namespace App\Http\ResponseFormatter;

use App\Application\UseCase\CheckoutProvideIban\CheckoutProvideIbanResponse;
use OpenApi\Annotations as OA;
use Ozean12\Support\Serialization\ArrayableInterface;

/**
 * @OA\Schema(schema="CheckoutProvideIbanResponse", title="Provide IBAN for checkout session response", type="object",
 *     properties={
 *      @OA\Property(property="iban", ref="#/components/schemas/IBAN", nullable=false),
 *      @OA\Property(property="mandate_reference", ref="#/components/schemas/TinyText", nullable=false),
 *      @OA\Property(property="creditor_name", ref="#/components/schemas/TinyText", nullable=false),
 *      @OA\Property(property="creditor_identifier", ref="#/components/schemas/TinyText", nullable=false),
 * })
 */
class CheckoutProvideIbanResponsePayload implements ArrayableInterface
{
    private CheckoutProvideIbanResponse $response;

    public function __construct(CheckoutProvideIbanResponse $response)
    {
        $this->response = $response;
    }

    public function toArray(): array
    {
        $mandate = $this->response->getMandate();

        return [
            'iban' => $mandate->getBankAccount()->getIban()->toString(),
            'mandate_reference' => $mandate->getMandateReference(),
            'creditor_name' => $this->response->getCreditorName(),
            'creditor_identifier' => $mandate->getCreditorIdentification(),
        ];
    }
}
