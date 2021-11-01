<?php

declare(strict_types=1);

namespace App\Application\UseCase\OrderExpiration;

class UpdateStateForExpiredOrdersCommand
{
    private \DateTimeInterface $limit;

    public function __construct(\DateTimeInterface $limit)
    {
        $this->limit = $limit;
    }

    public function getLimit(): \DateTimeInterface
    {
        return $this->limit;
    }
}
