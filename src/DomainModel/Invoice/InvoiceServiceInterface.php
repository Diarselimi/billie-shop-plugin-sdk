<?php

declare(strict_types=1);

namespace App\DomainModel\Invoice;

interface InvoiceServiceInterface
{
    public function getByUuids(array $uuids): InvoiceCollection;

    public function getOneByUuid(string $uuid): ?Invoice;
}
