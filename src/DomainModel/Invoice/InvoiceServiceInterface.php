<?php

declare(strict_types=1);

namespace App\DomainModel\Invoice;

interface InvoiceServiceInterface
{
    /**
     * @param  array|string[] $uuids
     * @return Invoice[]
     */
    public function findByUuids(array $uuids): array;
}
