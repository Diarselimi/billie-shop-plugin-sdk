<?php

declare(strict_types=1);

namespace App\DomainModel\PaymentMethod;

use Ozean12\Support\Collections\ArrayCollection;

/**
 * @method PaymentMethod[] toArray()
 * @method PaymentMethod[]|\ArrayIterator getIterator()
 */
class PaymentMethodCollection extends ArrayCollection
{
    public function getSelectedPaymentMethod(): ?string
    {
        if ($this->isEmpty()) {
            return null;
        }

        foreach ($this->items as $paymentMethod) {
            /** @var PaymentMethod $paymentMethod */
            if ($paymentMethod->isDirectDebit()) {
                return PaymentMethod::TYPE_DIRECT_DEBIT;
            }
        }

        return PaymentMethod::TYPE_BANK_TRANSFER;
    }
}
