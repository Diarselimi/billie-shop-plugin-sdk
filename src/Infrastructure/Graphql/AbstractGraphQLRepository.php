<?php

declare(strict_types=1);

namespace App\Infrastructure\Graphql;

use Ozean12\GraphQLBundle\Query;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\Config\FileLocator;

abstract class AbstractGraphQLRepository extends \Ozean12\GraphQLBundle\AbstractGraphQLRepository implements LoggingInterface
{
    use LoggingTrait;

    protected $graphQL;

    protected static $resourcePaths = [__DIR__ . '/queries'];

    protected function executeQuery(string $name, array $params = []): array
    {
        $query = new Query($this->getResourceAsString("{$name}.graphql"), $params);

        return iterator_to_array($this->hydrateToCollection($query));
    }

    protected function getResourceAsString(string $name): string
    {
        return file_get_contents((new FileLocator(self::$resourcePaths))->locate($name));
    }

    protected function query(string $name, array $params): array
    {
        $response = $this->executeQuery($name, $params);
        $total = $response['total'] ?? count($response);
        $this->logInfo('GraphQL "' . $name . '" query', ['params' => $params, 'total_results' => $total, 'response' => $response]);

        return $response;
    }
}
