<?php

namespace App\Infrastructure\OpenApi\Annotations\Processors;

use App\Http\Controller\ApiDocs\AbstractApiDocsController;
use OpenApi\Analysis;
use OpenApi\Annotations as OA;

class AddServers implements ProcessorInterface
{
    private array $groups;

    public function __construct(array $groups)
    {
        $this->groups = $groups;
    }

    private function getServers(): array
    {
        $apiVersion = '/v1';
        if (in_array(AbstractApiDocsController::API_VERSION_2, $this->groups, true)) {
            $apiVersion = '/v2';
        }

        $testInstances = range(1, 19);
        $serverNumVar = new OA\ServerVariable(
            [
                'serverVariable' => 'num',
                'default' => '16',
                'enum' => array_map('strval', $testInstances),
                'description' => 'Test Server Instance',
            ]
        );

        $awsInstanceServerVar = new OA\ServerVariable(
            ['serverVariable' => 'instance', 'description' => 'AWS Gateway instance ID', 'default' => 'XXXXX']
        );

        return [
            new OA\Server([
                'url' => 'http://paella-core.test{num}.ozean12.com',
                'description' => 'Test API (No Gateway)',
                'variables' => [$serverNumVar],
                'x' => ['groups' => ['support', 'salesforce']],
            ]),
            new OA\Server([ // https://private-api.paella.ozean12.com/test-10/healthcheck
                'url' => 'https://private-api.paella.ozean12.com/test-{num}',
                'description' => 'Test Private API (AWS Gateway)',
                'variables' => [$awsInstanceServerVar, $serverNumVar],
                'x' => ['groups' => ['support', 'salesforce']],
            ]),
            new OA\Server([ // https://private-api.paella.ozean12.com/test-10/healthcheck
                'url' => 'https://paella-private-api.billie.io/api',
                'description' => 'Production Private API (AWS Gateway)',
                'x' => ['groups' => ['support', 'salesforce']],
            ]),
            new OA\Server(['url' => 'https://paella-sandbox.billie.io/api'.$apiVersion, 'description' => 'Test Sandbox API']),
            new OA\Server(['url' => 'https://paella.billie.io/api'.$apiVersion, 'description' => 'Production API']),
        ];
    }

    public function __invoke(Analysis $analysis)
    {
        $servers = $this->getServers();

        /** @var OA\OpenApi $annotation */
        foreach ($analysis->getAnnotationsOfType(OA\OpenApi::class) as $annotation) {
            $annotation->servers = $servers;
        }
    }
}
