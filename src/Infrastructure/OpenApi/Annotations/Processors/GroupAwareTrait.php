<?php

declare(strict_types=1);

namespace App\Infrastructure\OpenApi\Annotations\Processors;

use OpenApi\Annotations as OA;

trait GroupAwareTrait
{
    /**
     * @param  OA\AbstractAnnotation|string[] $source
     * @param  array                          $allowedGroups
     * @return bool
     */
    private function isInAllowedGroups($source, array $allowedGroups): bool
    {
        if ($source instanceof OA\AbstractAnnotation) {
            $sourceGroups = $this->extractGroups($source);
        } elseif (is_array($source)) {
            $sourceGroups = $source;
        } else {
            return false;
        }

        if (empty($sourceGroups)) {
            // skip if x-groups property is not defined or empty
            return false;
        }

        foreach ($sourceGroups as $group) {
            if (in_array($group, $allowedGroups)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  OA\AbstractAnnotation|string[] $source
     * @param  array                          $filteredGroups
     * @return bool
     */
    private function isInFilteredGroups($source, array $filteredGroups): bool
    {
        if ($source instanceof OA\AbstractAnnotation) {
            $sourceGroups = $this->extractGroups($source);
        } elseif (is_array($source)) {
            $sourceGroups = $source;
        } else {
            return false;
        }

        if (empty($sourceGroups)) {
            // skip if x-groups property is not defined or empty
            return false;
        }

        foreach ($sourceGroups as $group) {
            if (in_array($group, $filteredGroups)) {
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

        return $x['groups'];
    }
}
