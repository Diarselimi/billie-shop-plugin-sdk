<?php

namespace App\DomainModel\FileService;

use App\DomainModel\AbstractServiceRequestException;

class FileServiceRequestException extends AbstractServiceRequestException
{
    public function getServiceName(): string
    {
        return 'file';
    }
}
