<?php

declare(strict_types=1);

namespace App\Infrastructure\Graphql;

abstract class AbstractSearchGraphQLDTO
{
    private $offset;

    private $limit;

    private $sortBy;

    private $sortDirection;

    private $searchString;

    public function getOffset(): string
    {
        return $this->offset;
    }

    public function setOffset(string $offset): self
    {
        $this->offset = $offset;

        return $this;
    }

    public function getLimit(): string
    {
        return $this->limit;
    }

    public function setLimit(string $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    public function getSortBy(): string
    {
        return $this->sortBy;
    }

    public function setSortBy(string $sortBy): self
    {
        $this->sortBy = $sortBy;

        return $this;
    }

    public function getSortDirection(): string
    {
        return $this->sortDirection;
    }

    public function setSortDirection(string $sortDirection): self
    {
        $this->sortDirection = $sortDirection;

        return $this;
    }

    public function getSearchString(): ?string
    {
        return $this->searchString;
    }

    public function setSearchString(?string $searchString): self
    {
        $this->searchString = $searchString;

        return $this;
    }
}
