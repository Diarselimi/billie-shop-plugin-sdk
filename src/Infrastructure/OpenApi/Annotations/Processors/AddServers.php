<?php

namespace App\Infrastructure\OpenApi\Annotations\Processors;

use OpenApi\Analysis;
use OpenApi\Annotations as OA;

class AddServers implements ProcessorInterface
{
    public const SERVERS = [
        'public' => [
            'https://paella-sandbox.billie.io/api/v1' => 'Test Sandbox',
            'https://paella.billie.io/api/v1' => 'Production',
        ],
        'private' => [
            'http://paella.dev.ozean12.com' => 'Local VM',
            'http://paella.test10.ozean12.com' => 'Test 10',
            'http://paella-core.dev.ozean12.com' => 'Local VM (Core)',
            'http://paella-core.test10.ozean12.com' => 'Test 10 (Core)',
        ],
    ];

    public function __invoke(Analysis $analysis)
    {
        $serverAnnotations = [];

        foreach (self::SERVERS as $group => $servers) {
            foreach ($servers as $url => $description) {
                $serverAnnotations[] = new OA\Server(['url' => $url, 'description' => $description, 'x' => ['groups' => [$group]]]);
            }
        }

        /** @var OA\OpenApi $annotation */
        foreach ($analysis->getAnnotationsOfType(OA\OpenApi::class) as $annotation) {
            $annotation->servers = $serverAnnotations;
        }
    }
}
