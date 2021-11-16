<?php

namespace App\DomainModel\Payment;

use App\Application\UseCase\GetMerchantPayments\GetMerchantPaymentsRequest;
use App\DomainModel\Payment\RequestDTO\SearchPaymentsDTO;
use App\Helper\String\StringSearch;

class SearchPaymentsDTOFactory
{
    private StringSearch $stringSearch;

    public function __construct(StringSearch $stringSearch)
    {
        $this->stringSearch = $stringSearch;
    }

    public function create(GetMerchantPaymentsRequest $request): SearchPaymentsDTO
    {
        return (new SearchPaymentsDTO())
            ->setMerchantPaymentUuid($request->getMerchantPaymentUuid())
            ->setTransactionUuid($request->getTransactionUuid())
            ->setPaymentDebtorUuid($request->getPaymentDebtorUuid())
            ->setIsAllocated($request->isAllocated())
            ->setIsOverpayment($request->isOverpayment())
            ->setOffset($request->getOffset())
            ->setLimit($request->getLimit())
            ->setSortBy($request->getSortBy())
            ->setSortDirection($request->getSortDirection())
            ->setSearchString($this->stringSearch->cleanString($request->getSearchKeyword()))
        /**
         * DISABLED due to slow query, implementation kept so it can be revisited,
         * search by company name is highly requested feature by many customers
            ->setSearchCompanyString($this->stringSearch->getGermanRegexpSearchKeyword(
                strtolower($request->getSearchKeyword())
            ))
         */
        ;
    }
}
