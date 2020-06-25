<?php

namespace App\Infrastructure\OpenApi\Annotations\Processors;

use OpenApi\Analysis;

interface ProcessorInterface
{
    public const HTTP_METHODS = ['head', 'options', 'trace', 'get', 'post', 'put', 'patch', 'delete'];

    public function __invoke(Analysis $analysis);
}
