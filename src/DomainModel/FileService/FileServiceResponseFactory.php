<?php

namespace App\DomainModel\FileService;

class FileServiceResponseFactory
{
    public function createFromArray(array $data)
    {
        return new FileServiceResponseDTO($data['id'], $data['name'], $data['path']);
    }
}
