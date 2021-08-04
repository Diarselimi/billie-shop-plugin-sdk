<?php

declare(strict_types=1);

namespace App\Http\Response\DTO;

use App\DomainModel\PaymentMethod\PaymentMethod;
use App\DomainModel\PaymentMethod\PaymentMethodCollection;
use OpenApi\Annotations as OA;
use Ozean12\Support\Formatting\DateFormat;
use Ozean12\Support\Serialization\ArrayableInterface;

/**
 * @OA\Schema(
 *     schema="PaymentMethodType",
 *     title="Payment Method Type",
 *     type="string",
 *     enum={"bank_transfer", "direct_debit"},
 *     example="bank_transfer"
 * )
 *
 * @OA\Schema(schema="PaymentMethod", title="Payment Method", type="object", properties={
 *      @OA\Property(property="type", ref="#/components/schemas/PaymentMethodType"),
 *      @OA\Property(property="data", type="object", description="Identified company", properties={
 *          @OA\Property(property="iban", ref="#/components/schemas/TinyText", nullable=false),
 *          @OA\Property(property="bic", ref="#/components/schemas/TinyText", nullable=false),
 *          @OA\Property(property="bank_name", ref="#/components/schemas/TinyText", nullable=true, description="Name of the Bank entity"),
 *          @OA\Property(property="mandate_reference", ref="#/components/schemas/TinyText", nullable=true, description="Direct Debit SEPA Mandate Reference"),
 *          @OA\Property(property="mandate_execution_date", ref="#/components/schemas/DateTime", nullable=true, description="Execution Date of the Direct Debit SEPA mandate"),
 *          @OA\Property(property="creditor_identification", ref="#/components/schemas/TinyText", nullable=true, description="Creditor Identification Code (for Direct Debit only)"),
 *      })
 * })
 *
 * @OA\Schema(schema="PaymentMethodCollection", title="Eligible Payment Methods",
 *          type="array",
 *          @OA\Items(ref="#/components/schemas/PaymentMethod")
 * )
 *
 */
class PaymentMethodDTO implements ArrayableInterface
{
    private PaymentMethod $paymentMethod;

    public function __construct(PaymentMethod $paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
    }

    public static function collectionToArray(PaymentMethodCollection $collection): array
    {
        return array_map(
            static fn (PaymentMethod $paymentMethod) => (new self($paymentMethod))->toArray(),
            $collection->toArray()
        );
    }

    public function toArray(): array
    {
        $data = [
            'type' => $this->paymentMethod->getType(),
            'data' => [
                'iban' => $this->paymentMethod->getBankAccount()->getIban()->toString(),
                'bic' => $this->paymentMethod->getBankAccount()->getBic(),
                'bank_name' => $this->paymentMethod->getBankAccount()->getBankName(),
            ],
        ];

        if ($this->paymentMethod->hasMandate()) {
            $mandate = $this->paymentMethod->getSepaMandate();
            $data['data']['mandate_reference'] = $mandate->getMandateReference();
            $data['data']['mandate_execution_date'] = $this->paymentMethod->hasSepaMandateExecutionDate() ?
                $this->paymentMethod->getSepaMandateExecutionDate()->format(DateFormat::FORMAT_YMD_HIS) : null;
            $data['data']['creditor_identification'] = $mandate->getCreditorIdentification();
        }

        return $data;
    }
}
