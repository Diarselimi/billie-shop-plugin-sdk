<?php

namespace App\Http\Controller\ApiDocs;

use App\Application\UseCase\ApiDocsRender\ApiDocsRenderUseCase;
use App\Application\UseCase\ApiSpecLoad\ApiSpecLoadUseCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;

abstract class AbstractApiDocsController
{
    private $docsRenderUseCase;

    private $specLoadUseCase;

    public const CACHE_MAX_DAYS_INLINE_SPEC = (3 * 24 * 60 * 60);

    public const CACHE_MAX_DAYS_URL_SPEC = (30 * 24 * 60 * 60);

    public const API_VERSION_PRIVATE = 'private';

    public const API_VERSION_1 = 'publicV1';

    public const API_VERSION_2 = 'publicV2';

    public function __construct(ApiDocsRenderUseCase $docsRenderUseCase, ApiSpecLoadUseCase $specLoadUseCase)
    {
        $this->docsRenderUseCase = $docsRenderUseCase;
        $this->specLoadUseCase = $specLoadUseCase;
    }

    abstract public function execute(Request $request): Response;

    public function createResponse(Request $request, string $apiGroup): Response
    {
        $isInlineSpec = $request->query->has('1');
        $inlineSpec = $isInlineSpec ? $this->getInlineSpec($apiGroup) : "''";

        $headers = ['Content-Type' => 'text/html'];
        if (!$request->query->has('nocache')) {
            $headers['Cache-Control'] = 'max-age=' .
                ($isInlineSpec ? self::CACHE_MAX_DAYS_INLINE_SPEC : self::CACHE_MAX_DAYS_URL_SPEC) . ', public';
        }

        $htmlDoc = $this->docsRenderUseCase->execute($inlineSpec);

        return new Response($htmlDoc, 200, $headers);
    }

    private function getInlineSpec(string $apiGroup): string
    {
        $spec = $this->specLoadUseCase->execute($apiGroup);
        $spec = Yaml::parse($spec, Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE);

        return json_encode($spec, JSON_PRETTY_PRINT);
    }
}
