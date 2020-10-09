<?php

namespace App\Infrastructure\OpenApi\Annotations\Processors;

use OpenApi\Analysis;
use OpenApi\Annotations as OA;

class RemoveGroups implements ProcessorInterface
{
    public function __invoke(Analysis $analysis)
    {
        foreach ((array) $analysis->openapi->paths as $annotation) {
            foreach (self::HTTP_METHODS as $method) {
                $this->removeGroups($annotation->{$method});
            }
            $this->removeGroups($annotation);
        }
        foreach ((array) $analysis->openapi->tags as $annotation) {
            $this->removeGroups($annotation);
        }
        foreach ((array) $analysis->openapi->components->schemas as $annotation) {
            $this->removeGroups($annotation);
        }
        foreach ((array) $analysis->openapi->components->responses as $annotation) {
            $this->removeGroups($annotation);
        }
        foreach ((array) $analysis->openapi->components->requestBodies as $annotation) {
            $this->removeGroups($annotation);
        }
        foreach ((array) $analysis->openapi->components->securitySchemes as $annotation) {
            $this->removeGroups($annotation);
        }
        foreach ((array) $analysis->openapi->components->parameters as $annotation) {
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
