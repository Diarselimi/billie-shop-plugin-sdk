<?php

declare(strict_types=1);

namespace App\DomainModel\FileService;

class FileSizeExceededException extends \RuntimeException
{
    protected $message = "Upload file size has been exceeded.";
}
