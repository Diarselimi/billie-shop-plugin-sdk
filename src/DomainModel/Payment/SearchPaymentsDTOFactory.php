<?php

namespace App\DomainModel\Payment;

use App\Application\UseCase\GetMerchantPayments\GetMerchantPaymentsRequest;
use App\DomainModel\Payment\RequestDTO\SearchPaymentsDTO;

class SearchPaymentsDTOFactory
{
    public function create(GetMerchantPaymentsRequest $request)
    {
        $sortBy = ($request->getSortBy() == 'priority') ? 'is_allocated ASC, transaction_date' : 'transaction_date';

        return (new SearchPaymentsDTO())
            ->setMerchantPaymentUuid($request->getMerchantPaymentUuid())
            ->setTransactionUuid($request->getTransactionUuid())
            ->setPaymentDebtorUuid($request->getPaymentDebtorUuid())
            ->setOffset($request->getOffset())
            ->setLimit($request->getLimit())
            ->setSortBy($sortBy)
            ->setSortDirection($request->getSortDirection())
            ->setKeyword($this->cleanString($request->getSearchKeyword()))
        ;
    }

    private function cleanString(string $string): string
    {
        $string = preg_replace('/[\'\"`]/', ' ', $string);

        return trim(preg_replace('/\s+/', ' ', $string));
    }
}
