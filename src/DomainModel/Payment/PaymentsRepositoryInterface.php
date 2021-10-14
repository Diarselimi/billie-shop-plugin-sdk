<?php

namespace App\DomainModel\Payment;

use App\DomainModel\Payment\RequestDTO\SearchPaymentsDTO;
use App\Support\PaginatedCollection;

interface PaymentsRepositoryInterface
{
    public function getPaymentDetails(string $merchantPaymentUuid, string $transactionUuid): BankTransactionDetails;

    public function searchMerchantPayments(SearchPaymentsDTO $paymentsDTO): PaginatedCollection;

    public function getTicketPayments(string $paymentTicketUuid): PaginatedCollection;
}
