<?php

namespace App\Infrastructure\OpenApi\Annotations\Processors;

use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use const OpenApi\UNDEFINED;

/**
 * Excludes all elements that don't belong to any of the groups given in the constructor.
 * Elements that have no defined groups won't be excluded.
 *
 * Groups should be defined in the 'x-groups' Open API vendor extension, as array of strings.
 */
class FilterByGroups implements ProcessorInterface
{
    private $groups;

    public function __construct(array $groups)
    {
        $this->groups = $groups;
    }

    public function __invoke(Analysis $analysis)
    {
        if (empty($this->groups)) {
            return;
        }
        $this->filterFromList($analysis->openapi->servers);
        $this->filterFromList($analysis->openapi->tags);
        $this->filterFromList($analysis->openapi->paths);
        $this->filterFromList($analysis->openapi->components->schemas);
        $this->filterFromList($analysis->openapi->components->responses);
        $this->filterFromList($analysis->openapi->components->requestBodies);
        $this->filterFromList($analysis->openapi->components->parameters);
        $this->filterFromList($analysis->openapi->components->headers);
        $this->filterFromList($analysis->openapi->components->securitySchemes);
        $this->filterFromList($analysis->openapi->components->callbacks);
        $this->filterFromList($analysis->openapi->components->links);
        $this->filterFromList($analysis->openapi->components->examples);

        if (is_array($analysis->openapi->x) && isset($analysis->openapi->x['tagGroups'])) {
            foreach ($analysis->openapi->x['tagGroups'] as $i => $tagGroup) {
                if (isset($tagGroup['groups']) && is_array($tagGroup['groups']) && $this->isFilteredOut($tagGroup['groups'])) {
                    unset($analysis->openapi->x['tagGroups'][$i]);
                }
                if (isset($tagGroup['groups'])) {
                    unset($analysis->openapi->x['tagGroups'][$i]['groups']);
                }
            }
        }
    }

    private function filterFromList(&$node)
    {
        if ($node === UNDEFINED || !is_array($node)) {
            return;
        }
        foreach ($node as $i => $item) {
            if ($this->isFilteredOut($item)) {
                unset($node[$i]);

                continue;
            }
            if ($item instanceof OA\PathItem) {
                $this->filterFromPathItem($node, $i, $item);

                continue;
            }

            continue;
        }
    }

    private function filterFromPathItem(&$node, $path, OA\PathItem $item)
    {
        $this->filterFromList($item->parameters);
        $this->filterFromList($item->servers);
        $shouldRemovePathItem = true;
        foreach (['head', 'options', 'trace', 'get', 'post', 'put', 'patch', 'delete'] as $method) {
            $operation = $item->{$method};
            if ($this->isFilteredOut($operation)) {
                $item->{$method} = UNDEFINED;
            } elseif ($operation instanceof OA\Operation) {
                $shouldRemovePathItem = false;
            }
        }
        if ($shouldRemovePathItem) {
            unset($node[$path]);
        }
    }

    /**
     * @param  OA\AbstractAnnotation|string[] $source
     * @return bool
     */
    private function isFilteredOut($source): bool
    {
        if ($source instanceof OA\AbstractAnnotation) {
            $groups = $this->extractGroups($source);
        } elseif (is_array($source)) {
            $groups = $source;
        } else {
            return false;
        }

        if (empty($groups)) {
            // skip if x-groups property is not defined or empty
            return false;
        }

        foreach ($groups as $group) {
            if (in_array($group, $this->groups)) {
                return false;
            }
        }

        return true;
    }

    private function extractGroups(OA\AbstractAnnotation $annotation): array
    {
        $x = $annotation->x;

        if (!is_array($x) || !isset($x['groups']) || !is_array($x['groups']) || empty($x['groups'])) {
            // skip if x-groups property is not defined or empty
            return [];
        }

        unset($annotation->x['groups']);

        return $x['groups'];
    }
}
