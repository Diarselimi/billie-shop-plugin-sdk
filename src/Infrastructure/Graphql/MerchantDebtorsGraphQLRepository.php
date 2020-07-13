<?php

declare(strict_types=1);

namespace App\Infrastructure\Graphql;

use App\DomainModel\MerchantDebtor\SearchMerchantDebtorsDTO;
use App\DomainModel\MerchantDebtor\SearchMerchantDebtorsRepositoryInterface;
use App\Support\PaginatedCollection;

class MerchantDebtorsGraphQLRepository extends AbstractGraphQLRepository implements SearchMerchantDebtorsRepositoryInterface
{
    private const GET_MERCHANTS_QUERY = 'get_merchant_debtors';

    private const GET_MERCHANTS_TOTAL_QUERY = 'get_merchant_debtors_total';

    public function searchMerchantDebtors(SearchMerchantDebtorsDTO $dto): PaginatedCollection
    {
        $params = [
            'merchantId' => $dto->getMerchantId(),
            'changeRequestStates' => $dto->getChangeRequestStates(),
            'offset' => $dto->getOffset(),
            'limit' => $dto->getLimit(),
            'sortBy' => $dto->getSortBy(),
            'sortDirection' => $dto->getSortDirection(),
            'searchString' => $dto->getSearchString(),
        ];

        $countParams = [
            'merchantId' => $dto->getMerchantId(),
            'changeRequestStates' => $dto->getChangeRequestStates(),
            'searchString' => $dto->getSearchString(),
        ];

        $countResult = $this->query(self::GET_MERCHANTS_TOTAL_QUERY, $countParams);
        $total = $countResult[0]['total'] ?? 0;

        return new PaginatedCollection($this->query(self::GET_MERCHANTS_QUERY, $params), $total);
    }
}
