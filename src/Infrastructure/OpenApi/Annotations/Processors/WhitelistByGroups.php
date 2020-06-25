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
class WhitelistByGroups implements ProcessorInterface
{
    use GroupAwareTrait;

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
        $this->filterTagGroups($analysis->openapi->x);
    }

    private function filterTagGroups(&$ext)
    {
        if (!is_array($ext) && !isset($ext['tagGroups'])) {
            return;
        }
        foreach ($ext['tagGroups'] as $i => $tagGroup) {
            if (isset($tagGroup['groups'])
                && is_array($tagGroup['groups'])
                && $this->isInFilteredGroups($tagGroup['groups'], $this->groups)
            ) {
                unset($ext['tagGroups'][$i]);

                continue;
            }
            if (isset($tagGroup['groups'])) {
                unset($ext['tagGroups'][$i]['groups']);
            }
        }
        $ext['tagGroups'] = array_values($ext['tagGroups']);
    }

    private function filterFromList(&$node)
    {
        if ($node === UNDEFINED || !is_array($node)) {
            return;
        }
        $isNumericIndexed = true;

        foreach ($node as $i => $item) {
            $isNumericIndexed = $isNumericIndexed && is_numeric($i);

            if ($this->isInFilteredGroups($item, $this->groups)) {
                unset($node[$i]);

                continue;
            }
            if ($item instanceof OA\PathItem) {
                $this->filterFromPathItem($node, $i, $item);

                continue;
            }

            continue;
        }

        if ($isNumericIndexed) {
            $node = array_values($node);
        }
    }

    private function filterFromPathItem(&$node, $path, OA\PathItem $item)
    {
        $this->filterFromList($item->parameters);
        $this->filterFromList($item->servers);
        $shouldRemovePathItem = true;
        foreach (self::HTTP_METHODS as $method) {
            /** @var OA\AbstractAnnotation $operation */
            $operation = $item->{$method};

            if ($this->isInFilteredGroups($operation, $this->groups)) {
                $item->{$method} = UNDEFINED;
            } elseif ($operation instanceof OA\Operation) {
                $shouldRemovePathItem = false;
            }
        }
        if ($shouldRemovePathItem) {
            unset($node[$path]);
        }
    }
}
