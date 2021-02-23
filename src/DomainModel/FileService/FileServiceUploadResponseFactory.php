<?php

declare(strict_types=1);

namespace App\DomainModel\FileService;

class FileServiceUploadResponseFactory
{
    public function createFromArray(array $data): FileServiceUploadResponse
    {
        return new FileServiceUploadResponse((int) $data['id'], $data['uuid'], $data['name'], $data['path']);
    }
}
