<?php

declare(strict_types=1);

namespace App\DomainModel\CompanySimilarity;

class CompanySimilarityServiceException extends \RuntimeException
{
    protected $message = 'Looking for similar companies has failed';
}
