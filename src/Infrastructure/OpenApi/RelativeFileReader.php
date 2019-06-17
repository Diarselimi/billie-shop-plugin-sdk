<?php

namespace App\Infrastructure\OpenApi;

class RelativeFileReader
{
    private $rootDir;

    public function __construct(string $rootDir)
    {
        $this->rootDir = $rootDir;
    }

    public function getFullPath(string $relativeFile): string
    {
        return $this->rootDir . '/' . $relativeFile;
    }

    public function read(string $relativeFile): string
    {
        $filePath = $this->getFullPath($relativeFile);

        if (!$this->exists($relativeFile)) {
            throw new \Exception("File {$filePath} cannot be read or does not exist.");
        }

        return file_get_contents($filePath);
    }

    public function exists(string $relativeFile): bool
    {
        $filePath = $this->getFullPath($relativeFile);

        return (!file_exists($filePath) || !is_readable($filePath) || !is_file($filePath)) ? false : true;
    }
}
