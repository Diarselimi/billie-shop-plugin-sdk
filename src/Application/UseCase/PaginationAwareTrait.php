<?php

namespace App\Application\UseCase;

/**
 * @mixin PaginationAwareInterface
 */
trait PaginationAwareTrait
{
    /**
     * @Assert\Type(type="int")
     * @Assert\GreaterThanOrEqual(value=0)
     */
    private $offset;

    /**
     * @Assert\Type(type="int")
     * @Assert\GreaterThan(value=0)
     * @Assert\LessThanOrEqual(value=100)
     */
    private $limit;

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @return $this
     */
    public function setOffset(int $value)
    {
        $this->offset = $value;

        return $this;
    }

    /**
     * @return $this
     */
    public function setLimit(int $limit)
    {
        $this->limit = $limit;

        return $this;
    }
}
