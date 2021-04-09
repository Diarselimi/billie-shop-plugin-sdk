<?php

declare(strict_types=1);

namespace App\DomainModel\Invoice\CreditNote;

use App\DomainModel\Invoice\Invoice;
use App\Support\AbstractFactory;
use Ozean12\Money\Money;
use Ozean12\Money\TaxedMoney\TaxedMoney;
use Ramsey\Uuid\Uuid;

class CreditNoteFactory extends AbstractFactory
{
    public function createFromArray(array $data): CreditNote
    {
        $gross = new Money($data['amount_gross']);
        $net = new Money($data['amount_net']);
        $tax = $gross->subtract($net);

        return (new CreditNote())
            ->setAmount(new TaxedMoney($gross, $net, $tax))
            ->setExternalCode($data['external_code'])
            ->setExternalComment($data['external_comment'])
            ->setInternalComment($data['internal_comment'])
            ->setCreatedAt(new \DateTime($data['created_at']))
            ->setUuid($data['uuid']);
    }

    public function create(
        Invoice $invoice,
        TaxedMoney $amount,
        ?string $externalCode,
        ?string $internalComment
    ): CreditNote {
        return (new CreditNote())
            ->setAmount($amount)
            ->setCreatedAt(new \DateTime())
            ->setExternalCode($externalCode)
            ->setExternalComment(null)
            ->setInternalComment($internalComment)
            ->setUuid(Uuid::uuid4()->toString())
            ->setInvoiceUuid($invoice->getUuid());
    }
}
