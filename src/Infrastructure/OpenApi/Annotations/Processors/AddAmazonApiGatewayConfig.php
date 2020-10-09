<?php

declare(strict_types=1);

namespace App\Infrastructure\OpenApi\Annotations\Processors;

use OpenApi\Analysis;
use OpenApi\Annotations as OA;

class AddAmazonApiGatewayConfig implements ProcessorInterface
{
    use GroupAwareTrait;

    private const X_PARAM_KEY = 'amazon-apigateway-integration';

    private const BASE_CONFIG = [
        'uri' => 'http://$${stageVariables.lbDnsName}:${paella_port}/public/',
        'httpMethod' => null,
        'type' => 'http_proxy',
        'connectionId' => '$${stageVariables.vpcLinkId}',
        'connectionType' => 'VPC_LINK',
        'passthroughBehavior' => 'when_no_templates',
        'requestParameters' => [],
    ];

    private $groups = [self::X_PARAM_KEY];

    public function __invoke(Analysis $analysis)
    {
        foreach ($analysis->openapi->paths as $i => $pathItem) {
            foreach (self::HTTP_METHODS as $method) {
                /** @var OA\AbstractAnnotation $op */
                $op = $pathItem->{$method};

                if (!($op instanceof OA\Operation) || !$this->isInAllowedGroups($op, $this->groups)) {
                    continue;
                }

                /** @var OA\Operation $op */
                $config = self::BASE_CONFIG;
                $config['uri'] .= ltrim($op->path, '/');
                $config['httpMethod'] = strtoupper($op->method);

                foreach ((array) $op->parameters as $param) {
                    if (!is_object($param)) {
                        continue;
                    }
                    $config['requestParameters']['integration.request.path.' . $param->name] =
                        'method.request.path.' . $param->name;
                }

                if (empty($config['requestParameters'])) {
                    unset($config['requestParameters']);
                }

                $analysis->openapi->paths[$i]->{$method}->x[self::X_PARAM_KEY] = $config;
            }
        }
    }
}
