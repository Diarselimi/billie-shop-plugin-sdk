<?php

declare(strict_types=1);

namespace App\DomainModel\CompanySimilarity;

interface CompanySimilarityServiceInterface
{
    public function match(array $input, array $candidate): array;
}
