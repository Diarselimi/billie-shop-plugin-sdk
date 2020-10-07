<?php

namespace App\Application\UseCase\ApiSpecLoad;

use App\Infrastructure\OpenApi\RelativeFileReader;

class ApiSpecLoadUseCase
{
    private $apiSpecFileReader;

    private $resourcesFileReader;

    public function __construct(RelativeFileReader $apiSpecFileReader, RelativeFileReader $resourcesFileReader)
    {
        $this->apiSpecFileReader = $apiSpecFileReader;
        $this->resourcesFileReader = $resourcesFileReader;
    }

    public function execute(string $specGroupName = 'all'): string
    {
        $filename = "paella-core-openapi-{$specGroupName}.yaml";

        if ($this->apiSpecFileReader->exists($filename)) {
            $spec = $this->apiSpecFileReader->read($filename);

            return $this->embedImages($spec);
        }

        throw new ApiSpecNotFoundException($specGroupName);
    }

    private function embedImages(string $spec): string
    {
        preg_match_all('/\\(src\\/Resources\\/docs\\/(.*\\.png)\\)/', $spec, $matches);
        if (count($matches) < 2) {
            return $spec;
        }
        array_shift($matches);
        foreach ($matches as $match) {
            if (!isset($match[0])) {
                continue;
            }
            $base64 = base64_encode($this->resourcesFileReader->read('docs/' . $match[0]));
            $spec = str_replace(
                "(src/Resources/docs/{$match[0]})",
                '(data:image/png;base64,' . $base64 . ')',
                $spec
            );
        }

        return $spec;
    }
}
