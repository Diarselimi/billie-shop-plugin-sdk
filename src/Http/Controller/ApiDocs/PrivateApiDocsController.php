<?php

namespace App\Http\Controller\ApiDocs;

use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Get(
 *     path="/internal-docs",
 *     operationId="docs_internal_get_default",
 *     summary="Internal API Docs (default)",
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
class PrivateApiDocsController extends AbstractApiDocsController
{
    public function execute(Request $request): Response
    {
        return $this->createResponse($request, 'full');
    }
}
