<?php

declare(strict_types=1);

namespace App\Application\UseCase\GetMerchantPaymentDetails;

use App\DomainModel\OrderInvoice\OrderInvoiceCollection;
use App\DomainModel\Payment\BankTransactionDetails;
use App\DomainModel\PaymentMethod\PaymentMethod;
use Ozean12\Borscht\Client\DomainModel\BankTransaction\BankTransaction;

final class GetMerchantPaymentDetailsResponse
{
    private BankTransactionDetails $transactionDetails;

    private ?PaymentMethod $paymentMethod;

    private OrderInvoiceCollection $orderInvoicesCollection;

    private BankTransaction $transaction;

    public function __construct(
        BankTransactionDetails $transactionDetails,
        ?PaymentMethod $paymentMethod,
        OrderInvoiceCollection $orderInvoicesCollection,
        BankTransaction $transaction
    ) {
        $this->transactionDetails = $transactionDetails;
        $this->paymentMethod = $paymentMethod;
        $this->orderInvoicesCollection = $orderInvoicesCollection;
        $this->transaction = $transaction;
    }

    public function getTransactionDetails(): BankTransactionDetails
    {
        return $this->transactionDetails;
    }

    public function getPaymentMethod(): ?PaymentMethod
    {
        return $this->paymentMethod;
    }

    public function getOrderInvoicesCollection(): OrderInvoiceCollection
    {
        return $this->orderInvoicesCollection;
    }

    public function getTransaction(): BankTransaction
    {
        return $this->transaction;
    }
}
