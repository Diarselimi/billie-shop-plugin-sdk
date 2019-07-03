<?php

namespace App\Http\Controller\PrivateApi;

use App\Application\UseCase\ApiDocsRender\ApiDocsRenderUseCase;
use App\Application\UseCase\ApiSpecLoad\ApiSpecLoadUseCase;
use App\Http\Controller\AbstractApiDocsController;
use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *     path="/internal-docs/{apiGroup}",
 *     operationId="docs_internal_get_by_group",
 *     summary="Internal API Docs",
 *
 *     tags={"Docs"},
 *     x={"groups":{"support"}},
 *
 *     @OA\Parameter(in="path", name="apiGroup", description="API group name", required=true,
 *          @OA\Schema(ref="#/components/schemas/PrivateApiGroup")
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
class PrivateApiDocsController extends AbstractApiDocsController
{
    public function __construct(
        ApiDocsRenderUseCase $docsRenderUseCase,
        ApiSpecLoadUseCase $specLoadUseCase
    ) {
        parent::__construct($docsRenderUseCase, $specLoadUseCase, PrivateApiSpecController::API_GROUP_WHITELIST);
    }
}
