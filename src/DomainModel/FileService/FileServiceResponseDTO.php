<?php

namespace App\DomainModel\FileService;

class FileServiceResponseDTO
{
    private $fileId;

    private $fileName;

    private $filePath;

    public function __construct(int $fileId, string $fileName, string $filePath)
    {
        $this->fileId = $fileId;
        $this->fileName = $fileName;
        $this->filePath = $filePath;
    }

    public function getFileId(): int
    {
        return $this->fileId;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }
}
