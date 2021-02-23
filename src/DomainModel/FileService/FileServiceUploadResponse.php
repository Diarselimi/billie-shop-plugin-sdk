<?php

namespace App\DomainModel\FileService;

class FileServiceUploadResponse
{
    private $id;

    private $fileName;

    private $filePath;

    private $uuid;

    public function __construct(int $id, string $uuid, string $fileName, string $filePath)
    {
        $this->id = $id;
        $this->uuid = $uuid;
        $this->fileName = $fileName;
        $this->filePath = $filePath;
    }

    /**
     * @deprecated
     */
    public function getFileId(): int
    {
        return $this->id;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }
}
