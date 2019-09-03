<?php

namespace App\Application\UseCase\GetMerchantDebtors;

use App\Application\UseCase\PaginationAwareInterface;
use App\Application\UseCase\PaginationAwareTrait;
use App\Application\UseCase\ValidatedRequestInterface;
use Symfony\Component\Validator\Constraints as Assert;

class GetMerchantDebtorsRequest implements ValidatedRequestInterface, PaginationAwareInterface
{
    use PaginationAwareTrait;

    const DEFAULT_SORT_FIELD = 'created_at';

    const DEFAULT_SORT_DIRECTION = 'DESC';

    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="int")
     */
    private $merchantId;

    /**
     * @Assert\Type(type="string")
     * @Assert\Choice({"created_at", "external_code"})
     */
    private $sortBy;

    /**
     * @Assert\Type(type="string")
     * @Assert\Choice({"DESC", "ASC"})
     */
    private $sortDirection;

    /**
     * @Assert\Type(type="string")
     */
    private $searchString;

    public function __construct(
        int $merchantId,
        int $offset,
        int $limit,
        string $sortBy,
        string $sortDirection,
        ?string $searchString
    ) {
        $this->merchantId = $merchantId;
        $this->offset = $offset;
        $this->limit = $limit;
        $this->sortBy = $sortBy;
        $this->sortDirection = $sortDirection;
        $this->searchString = $searchString;
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

    public function getSearchString(): ? string
    {
        return $this->searchString;
    }
}
