<?php

namespace App\Http\Controller;

use App\Application\UseCase\ApiDocsRender\ApiDocsRenderUseCase;
use App\Application\UseCase\ApiSpecLoad\ApiSpecLoadUseCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Yaml\Yaml;

abstract class AbstractApiDocsController
{
    private $apiGroupWhitelist;

    private $docsRenderUseCase;

    private $specLoadUseCase;

    public const CACHE_MAX_DAYS_INLINE_SPEC = (3 * 24 * 60 * 60);

    public const CACHE_MAX_DAYS_URL_SPEC = (30 * 24 * 60 * 60);

    public function __construct(
        ApiDocsRenderUseCase $docsRenderUseCase,
        ApiSpecLoadUseCase $specLoadUseCase,
        array $apiGroupWhitelist
    ) {
        $this->docsRenderUseCase = $docsRenderUseCase;
        $this->specLoadUseCase = $specLoadUseCase;
        $this->apiGroupWhitelist = $apiGroupWhitelist;
    }

    public function execute(Request $request, string $apiGroup = null): Response
    {
        if (!$apiGroup || !in_array($apiGroup, $this->apiGroupWhitelist)) {
            throw new NotFoundHttpException('API specification not found.');
        }

        $isInlineSpec = !$request->query->has('noembed');

        $spec = $isInlineSpec
            ? $this->getInlineSpec($apiGroup)
            : "'" . $this->getSpecUrl($apiGroup, $request) . "'";

        $headers = ['Content-Type' => 'text/html'];
        if (!$request->query->has('nocache')) {
            $headers['Cache-Control'] = 'max-age=' .
                ($isInlineSpec ? self::CACHE_MAX_DAYS_INLINE_SPEC : self::CACHE_MAX_DAYS_URL_SPEC) . ', public';
        }

        $htmlDoc = $this->docsRenderUseCase->execute($spec);

        return new Response($htmlDoc, 200, $headers);
    }

    private function getInlineSpec(string $apiGroup): string
    {
        $spec = $this->specLoadUseCase->execute($apiGroup);
        $spec = Yaml::parse($spec, Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE);

        return json_encode($spec, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    private function getSpecUrl(string $apiGroup, Request $request): string
    {
        $basePath = rtrim($request->getPathInfo(), '/');

        if (!strpos($basePath, 'docs/' . $apiGroup)) {
            $basePath = "{$basePath}/$apiGroup";
        }

        return $request->getUriForPath("{$basePath}/billie-pad-openapi.yaml");
    }
}
