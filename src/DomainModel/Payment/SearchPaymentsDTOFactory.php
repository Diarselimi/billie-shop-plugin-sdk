<?php

namespace App\DomainModel\Payment;

use App\Application\UseCase\GetMerchantPayments\GetMerchantPaymentsRequest;
use App\DomainModel\Payment\RequestDTO\SearchPaymentsDTO;
use App\Helper\String\StringSearch;

class SearchPaymentsDTOFactory
{
    private $stringSearch;

    public function __construct(StringSearch $stringSearch)
    {
        $this->stringSearch = $stringSearch;
    }

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
            ->setSearchString($this->stringSearch->cleanString($request->getSearchKeyword()))
            ->setSearchCompanyString($this->stringSearch->getGermanRegexpSearchKeyword(
                strtolower($request->getSearchKeyword())
            ))
        ;
    }
}
