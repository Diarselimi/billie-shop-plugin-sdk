<?php

namespace App\Http\Controller\PrivateApi;

use App\Application\UseCase\ApiDocsRender\ApiDocsRenderUseCase;
use App\Application\UseCase\ApiSpecLoad\ApiSpecLoadUseCase;
use App\Http\Controller\AbstractApiDocsController;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Get(
 *     path="/internal-docs",
 *     operationId="docs_internal_get_default",
 *     summary="Internal API Docs (default)",
 *
 *     tags={"Docs"},
 *     x={"groups":{"support"}},
 *
 *     @OA\Parameter(in="query", name="noembed", description="Use spec URL instead of inline JSON", required=false,
 *          @OA\Schema(type="boolean"), example=1
 *     ),
 *
 *     @OA\Parameter(in="query", name="nocache", description="Do not send cache headers to the client", required=false,
 *          @OA\Schema(type="boolean"), example=1
 *     ),
 *
 *     @OA\Response(response=200, ref="#/components/responses/HtmlDocument"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class PrivateApiDocsDefaultController extends AbstractApiDocsController
{
    public function __construct(
        ApiDocsRenderUseCase $docsRenderUseCase,
        ApiSpecLoadUseCase $specLoadUseCase
    ) {
        parent::__construct($docsRenderUseCase, $specLoadUseCase, ['full']);
    }

    public function execute(Request $request, string $apiGroup = null): Response
    {
        return parent::execute($request, 'full');
    }
}
