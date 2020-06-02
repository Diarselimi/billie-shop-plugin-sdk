<?php

namespace App\DomainModel\FileService;

use Psr\Http\Message\StreamInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface FileServiceInterface
{
    public const TYPE_ORDER_INVOICE = 'order_invoice';

    public function upload(string $contents, string $filename, string $type): FileServiceResponseDTO;

    public function download(string $fileUuid): StreamInterface;

    public function uploadFromFile(UploadedFile $uploadedFile, string $filename, string $type): FileServiceResponseDTO;
}
