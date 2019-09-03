<?php

namespace App\Application\UseCase;

interface PaginationAwareInterface
{
    public const DEFAULT_LIMIT = 10;

    public function getOffset(): int;

    public function getLimit(): int;
}
