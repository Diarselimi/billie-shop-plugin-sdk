<?php

namespace App\Infrastructure\OpenApi\Annotations\Processors;

use OpenApi\Analysis;
use OpenApi\Annotations as OA;

class RemoveGroups implements ProcessorInterface
{
    public function __invoke(Analysis $analysis)
    {
        foreach ($analysis->openapi->paths as $annotation) {
            foreach (self::HTTP_METHODS as $method) {
                $this->removeGroups($annotation->{$method});
            }
            $this->removeGroups($annotation);
        }
        foreach ($analysis->openapi->tags as $annotation) {
            $this->removeGroups($annotation);
        }
        foreach ($analysis->openapi->components->schemas as $annotation) {
            $this->removeGroups($annotation);
        }
        foreach ($analysis->openapi->components->responses as $annotation) {
            $this->removeGroups($annotation);
        }
        foreach ($analysis->openapi->components->requestBodies as $annotation) {
            $this->removeGroups($annotation);
        }
        foreach ($analysis->openapi->components->securitySchemes as $annotation) {
            $this->removeGroups($annotation);
        }
        foreach ($analysis->openapi->components->parameters as $annotation) {
            $this->removeGroups($annotation);
        }
    }

    private function removeGroups(&$node): void
    {
        if (!($node instanceof OA\AbstractAnnotation)) {
            return;
        }

        if (isset($node->x['groups'])) {
            unset($node->x['groups']);
        }
    }
}
