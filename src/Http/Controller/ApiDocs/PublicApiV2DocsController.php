<?php

declare(strict_types=1);

namespace App\Http\Controller\ApiDocs;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *     path="/docs/v2",
 *     operationId="docs_v2_get_default",
 *     summary="Public API Docs (default)",
 *
 *     tags={"API Docs"},
 *     x={"groups":{"private"}},
 *
 *     @OA\Parameter(in="query", name="embed", description="Use inline JSON instead of the external file", required=false,
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
class PublicApiV2DocsController extends AbstractApiDocsController
{
    public function execute(Request $request): Response
    {
        return $this->createResponse($request, self::API_VERSION_2);
    }
}
