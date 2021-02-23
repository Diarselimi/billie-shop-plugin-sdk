<?php

declare(strict_types=1);

namespace App\DomainModel\FileService;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface FileServiceInterface
{
    public const TYPE_ORDER_INVOICE = 'order_invoice';

    public function upload(string $contents, string $filename, string $type): FileServiceUploadResponse;

    public function download(string $fileUuid): FileServiceDownloadResponse;

    public function uploadFromFile(UploadedFile $uploadedFile, string $filename, string $type): FileServiceUploadResponse;

    public function uploadFromUrl(
        string $url,
        string $filename,
        string $type,
        int $fileSizeLimit
    ): FileServiceUploadResponse;
}
