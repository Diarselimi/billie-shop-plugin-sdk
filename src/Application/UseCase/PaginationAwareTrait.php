<?php

namespace App\Application\UseCase;

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
}
