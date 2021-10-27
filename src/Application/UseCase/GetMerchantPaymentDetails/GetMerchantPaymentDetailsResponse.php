<?php

declare(strict_types=1);

namespace App\Application\UseCase\GetMerchantPaymentDetails;

use App\DomainModel\Payment\BankTransactionDetails;
use App\DomainModel\PaymentMethod\PaymentMethod;

final class GetMerchantPaymentDetailsResponse
{
    private BankTransactionDetails $transactionDetails;

    private ?PaymentMethod $paymentMethod;

    public function __construct(BankTransactionDetails $transactionDetails, ?PaymentMethod $paymentMethod)
    {
        $this->transactionDetails = $transactionDetails;
        $this->paymentMethod = $paymentMethod;
    }

    public function getTransactionDetails(): BankTransactionDetails
    {
        return $this->transactionDetails;
    }

    public function getPaymentMethod(): ?PaymentMethod
    {
        return $this->paymentMethod;
    }
}
