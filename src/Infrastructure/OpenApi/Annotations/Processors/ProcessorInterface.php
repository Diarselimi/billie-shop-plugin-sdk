<?php

namespace App\Infrastructure\OpenApi\Annotations\Processors;

use OpenApi\Analysis;

interface ProcessorInterface
{
    public function __invoke(Analysis $analysis);
}
