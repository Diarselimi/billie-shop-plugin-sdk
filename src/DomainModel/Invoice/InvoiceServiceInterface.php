<?php

declare(strict_types=1);

namespace App\DomainModel\Invoice;

interface InvoiceServiceInterface
{
    /**
     * @param  array|string[] $uuids
     * @return Invoice[]
     */
    public function getByUuids(array $uuids): array;

    public function getOneByUuid(string $uuid): ?Invoice;
}
