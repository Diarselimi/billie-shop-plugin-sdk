<?php

declare(strict_types=1);

namespace App\Infrastructure\Graphql;

use Ozean12\GraphQLBundle\Query;
use Symfony\Component\Config\FileLocator;

abstract class AbstractGraphQLRepository extends \Ozean12\GraphQLBundle\AbstractGraphQLRepository
{
    protected $graphQL;

    protected static $resourcePaths = [__DIR__ . '/queries'];

    public function executeQuery(string $name, array $params = []): array
    {
        $query = new Query($this->getResourceAsString("{$name}.graphql"), $params);

        return iterator_to_array($this->hydrateToCollection($query));
    }

    protected function getResourceAsString(string $name): string
    {
        return file_get_contents((new FileLocator(self::$resourcePaths))->locate($name));
    }
}
