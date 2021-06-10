<?php

declare(strict_types=1);

namespace App\DomainModel\Order\Search;

use Symfony\Component\HttpFoundation\ParameterBag;

class OrderSearchQuery
{
    private int $merchantId;

    private int $offset;

    private int $limit;

    private string $sortBy;

    private string $sortDirection;

    private ?string $searchString;

    private ParameterBag $filters;

    public function __construct(
        int $offset,
        int $limit,
        string $sortBy,
        string $sortDirection,
        int $merchantId,
        ?string $searchString,
        array $filters
    ) {
        $this->offset = $offset;
        $this->limit = $limit;
        $this->sortBy = $sortBy;
        $this->sortDirection = $sortDirection;
        $this->merchantId = $merchantId;
        $this->searchString = $searchString;
        $this->filters = new ParameterBag($filters);
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function getSortBy(): string
    {
        return $this->sortBy;
    }

    public function getSortDirection(): string
    {
        return $this->sortDirection;
    }

    public function getSearchString(): ?string
    {
        return $this->searchString;
    }

    public function hasSearchString(): bool
    {
        return !empty($this->searchString);
    }

    public function hasMerchantDebtorFilter(): bool
    {
        return $this->filters->has('merchant_debtor_id');
    }

    public function getMerchantDebtorFilter(): ?string
    {
        return $this->filters->get('merchant_debtor_id');
    }

    public function hasStateFilter(): bool
    {
        return $this->filters->has('state')
            && is_array($this->filters->get('state'))
            && !empty($this->filters->get('state'));
    }

    public function getStateFilter(): array
    {
        return $this->filters->get('state', []);
    }

    public function getFilters(): ParameterBag
    {
        return $this->filters;
    }
}
