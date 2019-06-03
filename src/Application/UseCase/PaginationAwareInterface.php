<?php

namespace App\Application\UseCase;

interface PaginationAwareInterface
{
    public function getOffset(): int;

    public function getLimit(): int;
}
