<?php

namespace App\Application\UseCase\GetOrders;

use App\Application\UseCase\PaginationAwareInterface;
use App\Application\UseCase\PaginationAwareTrait;
use App\Application\UseCase\ValidatedRequestInterface;
use Symfony\Component\Validator\Constraints as Assert;

class GetOrdersRequest implements ValidatedRequestInterface, PaginationAwareInterface
{
    use PaginationAwareTrait;

    const DEFAULT_LIMIT = 10;

    const DEFAULT_SORT_FIELD = 'created_at';

    const DEFAULT_SORT_DIRECTION = 'DESC';

    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="int")
     */
    private $merchantId;

    /**
     * @Assert\Type(type="string")
     * @Assert\Choice({"created_at", "amount_gross", "external_code", "state"})
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

    /**
     * @Assert\Collection(
     *     fields = {
     *         "merchant_debtor_id" = {
     *             @Assert\NotBlank,
     *             @Assert\Uuid()
     *         },
     *         "state" = {
     *             @Assert\Choice(choices=\App\DomainModel\Order\OrderStateManager::ALL_STATES, multiple=true, min=1)
     *         }
     *     },
     *     allowMissingFields = true
     * )
     */
    private $filters;

    public function __construct(
        int $merchantId,
        int $offset,
        int $limit,
        string $sortBy,
        string $sortDirection,
        ?string $searchString,
        array $filters
    ) {
        $this->merchantId = $merchantId;
        $this->offset = $offset;
        $this->limit = $limit;
        $this->sortBy = $sortBy;
        $this->sortDirection = $sortDirection;
        $this->searchString = $searchString;
        $this->filters = $filters;
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

    public function getFilters(): array
    {
        return $this->filters;
    }
}
