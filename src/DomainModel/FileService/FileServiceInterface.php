<?php

namespace App\DomainModel\FileService;

interface FileServiceInterface
{
    public function upload(string $contents, string $filename): FileServiceResponseDTO;
}
