<?php

namespace App\DomainModel\FileService;

interface FileServiceInterface
{
    public const TYPE_ORDER_INVOICE = 'order_invoice';

    public function upload(string $contents, string $filename, string $type): FileServiceResponseDTO;
}
