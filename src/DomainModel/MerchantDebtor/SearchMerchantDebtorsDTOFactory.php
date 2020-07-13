<?php

declare(strict_types=1);

namespace App\DomainModel\MerchantDebtor;

use App\Application\UseCase\GetMerchantDebtors\GetMerchantDebtorsRequest;
use App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestEntity;
use App\Helper\String\StringSearch;

class SearchMerchantDebtorsDTOFactory
{
    private const CHANGE_REQUEST_STATES = [
        DebtorInformationChangeRequestEntity::STATE_PENDING,
        DebtorInformationChangeRequestEntity::STATE_COMPLETE,
        DebtorInformationChangeRequestEntity::STATE_DECLINED,
    ];

    private $stringSearch;

    public function __construct(StringSearch $stringSearch)
    {
        $this->stringSearch = $stringSearch;
    }

    public function create(GetMerchantDebtorsRequest $request)
    {
        return (new SearchMerchantDebtorsDTO())
            ->setMerchantId($request->getMerchantId())
            ->setChangeRequestStates($this->stringSearch->getRegexpSearchKeyword(self::CHANGE_REQUEST_STATES))
            ->setOffset($request->getOffset() .'')
            ->setLimit($request->getLimit() .'')
            ->setSortBy($request->getSortBy())
            ->setSortDirection($request->getSortDirection())
            ->setSearchString($this->stringSearch->getGermanRegexpSearchKeyword(strtolower($request->getSearchString())));
    }
}
