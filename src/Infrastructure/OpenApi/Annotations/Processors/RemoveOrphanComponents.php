<?php

namespace App\Infrastructure\OpenApi\Annotations\Processors;

use OpenApi\Analysis;
use const OpenApi\UNDEFINED;

/**
 * Removes all components that haven't been used in the schema.
 */
class RemoveOrphanComponents implements ProcessorInterface
{
    private $specString;

    public function __invoke(Analysis $analysis)
    {
        do {
            $changed = false;
            $this->specString = $analysis->openapi->toJson();

            $changed = $changed || $this->filterFromList('schemas', 'schema', $analysis->openapi->components->schemas)
                || $this->filterFromList('responses', 'response', $analysis->openapi->components->responses)
                || $this->filterFromList('requestBodies', 'request', $analysis->openapi->components->requestBodies)
                || $this->filterFromList('parameters', 'parameter', $analysis->openapi->components->parameters)
                || $this->filterFromList('headers', 'header', $analysis->openapi->components->headers)
                // || $this->filterFromList('securitySchemes', 'securityScheme', $analysis->openapi->components->securitySchemes)
                || $this->filterFromList('links', 'link', $analysis->openapi->components->links);
        } while ($changed === true);
    }

    private function filterFromList(string $componentGroup, string $nameProperty, &$node): bool
    {
        if ($node === UNDEFINED || !is_array($node)) {
            return false;
        }

        $changed = false;

        foreach ($node as $i => $item) {
            $name = $item->{$nameProperty};
            $id = "#/components/{$componentGroup}/{$name}";

            if (strpos($this->specString, $id) === false) {
                unset($node[$i]);
                $changed = true;

                continue;
            }
        }

        return $changed;
    }
}
