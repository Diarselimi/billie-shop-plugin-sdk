<?php

declare(strict_types=1);

namespace App\DomainModel\Order\Search;

interface OrderSearchRepositoryInterface
{
    public function search(OrderSearchQuery $query): OrderSearchResult;
}
