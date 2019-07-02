<?php

namespace App\Infrastructure\OpenApi\Annotations\Processors;

use App\Infrastructure\OpenApi\RelativeFileReader;
use Illuminate\Support\Str;
use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use const OpenApi\UNDEFINED;

/**
 * Import the descriptions from Markdown files for Operation, Tag and Schema annotations.
 */
class AugmentDescriptions implements ProcessorInterface
{
    private $fileReader;

    public function __construct(RelativeFileReader $fileReader)
    {
        $this->fileReader = $fileReader;
    }

    public function __invoke(Analysis $analysis)
    {
        $annotationClasses = [
            OA\Operation::class,
            OA\Tag::class,
            OA\Schema::class,
            OA\Header::class,
            OA\SecurityScheme::class,
        ];

        foreach ($annotationClasses as $annotationClass) {
            foreach ($analysis->getAnnotationsOfType($annotationClass) as $annotation) {
                $this->importMarkdownDescription($annotation);
            }
        }
    }

    private function readMarkdown(string $folder, string $id): ?string
    {
        $fileName = "docs/markdown/{$folder}/{$id}.md";

        if (!$this->fileReader->exists($fileName)) {
            return null;
        }

        return trim($this->fileReader->read($fileName)) ?: null;
    }

    private function importMarkdownDescription(OA\AbstractAnnotation $annotation)
    {
        $id = null;
        $folder = 'components/' . Str::slug(Str::plural((new \ReflectionClass($annotation))->getShortName()));

        switch (true) {
            case $annotation instanceof OA\Tag:
                $id = $annotation->name;
                $folder = 'tags';

                break;
            case $annotation instanceof OA\Operation:
                $id = $annotation->operationId;
                $folder = 'operations';

                break;
            // Components:
            case $annotation instanceof OA\Schema:
                $id = $annotation->schema;

                break;
            case $annotation instanceof OA\Response:
                $id = $annotation->response;

                break;
            case $annotation instanceof OA\Parameter:
                $id = $annotation->parameter;

                break;
            case $annotation instanceof OA\RequestBody:
                $id = $annotation->request;

                break;
            case $annotation instanceof OA\Header:
                $id = $annotation->header;

                break;
            case $annotation instanceof OA\SecurityScheme:
                $id = $annotation->securityScheme;

                break;
            case $annotation instanceof OA\Link:
                $id = $annotation->link;

                break;
            default:
                return;
        }

        if (!$id || $id === UNDEFINED) {
            return;
        }

        $markdown = $this->readMarkdown($folder, Str::ucfirst(Str::camel($id)));

        if (!$markdown) {
            return;
        }

        $annotation->description = $markdown;
    }
}
