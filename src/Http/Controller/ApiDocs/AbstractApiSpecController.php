<?php

namespace App\Http\Controller\ApiDocs;

use App\Application\UseCase\ApiSpecLoad\ApiSpecLoadUseCase;
use App\Application\UseCase\ApiSpecLoad\ApiSpecNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class AbstractApiSpecController
{
    private $useCase;

    private $apiGroupWhitelist;

    public function __construct(ApiSpecLoadUseCase $useCase, array $apiGroupWhitelist)
    {
        $this->useCase = $useCase;
        $this->apiGroupWhitelist = $apiGroupWhitelist;
    }

    public function execute(Request $request, string $apiGroup): Response
    {
        if (!in_array($apiGroup, $this->apiGroupWhitelist)) {
            throw new NotFoundHttpException('API specification not found.');
        }

        try {
            $spec = $this->useCase->execute($apiGroup);
        } catch (ApiSpecNotFoundException $e) {
            throw new NotFoundHttpException('API specification not found.');
        }

        $headers = ['Content-Type' => 'text/x-yaml'];
        if (!$request->query->has('nocache')) {
            $headers['Cache-Control'] = 'max-age=' .
                AbstractApiDocsController::CACHE_MAX_DAYS_INLINE_SPEC . ', public';
        }

        return new Response($spec, 200, $headers);
    }
}
