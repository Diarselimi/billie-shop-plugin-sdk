<?php

namespace App\Application\UseCase\GetMerchantUsers;

use App\Application\UseCase\PaginationAwareInterface;
use App\Application\UseCase\PaginationAwareTrait;
use App\Application\UseCase\ValidatedRequestInterface;
use Symfony\Component\Validator\Constraints as Assert;

class GetMerchantUsersRequest implements ValidatedRequestInterface, PaginationAwareInterface
{
    use PaginationAwareTrait;

    const DEFAULT_SORT_FIELD = 'invitation_status';

    const DEFAULT_SORT_DIRECTION = 'ASC';

    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="int")
     */
    private $merchantId;

    /**
     * @Assert\Type(type="string")
     * @Assert\Choice({"invitation_status", "created_at"})
     */
    private $sortBy;

    /**
     * @Assert\Type(type="string")
     * @Assert\Choice({"DESC", "ASC"})
     */
    private $sortDirection;

    public function __construct(
        int $merchantId,
        int $offset,
        int $limit,
        string $sortBy,
        string $sortDirection
    ) {
        $this->merchantId = $merchantId;
        $this->offset = $offset;
        $this->limit = $limit;
        $this->sortBy = $sortBy;
        $this->sortDirection = $sortDirection;
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
}
