<?php

namespace App\Http\Controller\ApiDocs;

use App\Application\UseCase\ApiDocsRender\ApiDocsRenderUseCase;
use App\Application\UseCase\ApiSpecLoad\ApiSpecLoadUseCase;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Get(
 *     path="/docs",
 *     operationId="docs_get_default",
 *     summary="Public API Docs (default)",
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
 *
 * @OA\Get(
 *     path="/docs/{apiGroup}",
 *     operationId="docs_get_by_group",
 *     summary="Public API Docs",
 *
 *     tags={"Docs"},
 *     x={"groups":{"support"}},
 *
 *     @OA\Parameter(in="path", name="apiGroup", description="API group name", required=true,
 *          @OA\Schema(ref="#/components/schemas/PublicApiGroup")
 *     ),
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
class PublicApiDocsController extends AbstractApiDocsController
{
    public function __construct(
        ApiDocsRenderUseCase $docsRenderUseCase,
        ApiSpecLoadUseCase $specLoadUseCase
    ) {
        parent::__construct($docsRenderUseCase, $specLoadUseCase, PublicApiSpecController::API_GROUP_WHITELIST);
    }

    public function execute(Request $request, ?string $apiGroup = null): Response
    {
        return parent::execute($request, $apiGroup ?: 'standard');
    }
}
