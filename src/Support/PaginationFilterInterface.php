<?php

namespace App\Support;

interface PaginationFilterInterface
{
    public function check(array $item): bool;
}
