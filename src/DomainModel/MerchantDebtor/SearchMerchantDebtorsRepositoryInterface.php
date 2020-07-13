<?php

namespace App\DomainModel\MerchantDebtor;

use App\Support\PaginatedCollection;

interface SearchMerchantDebtorsRepositoryInterface
{
    public function searchMerchantDebtors(SearchMerchantDebtorsDTO $dto): PaginatedCollection;
}
