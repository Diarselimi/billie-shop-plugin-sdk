<?php

namespace App\Http\Controller\ApiDocs;

use App\Application\UseCase\ApiSpecLoad\ApiSpecLoadUseCase;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Get(
 *     path="/docs/public/billie-pad-openapi.yaml",
 *     operationId="docs_get_specs",
 *     summary="Public API Specs",
 *
 *     tags={"API Docs"},
 *     x={"groups":{"private"}},
 *
 *     @OA\Parameter(in="query", name="nocache", description="Do not send cache headers to the client", required=false,
 *          @OA\Schema(type="boolean"), example=1
 *     ),
 *
 *     @OA\Response(response=200, ref="#/components/responses/YamlDocument"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class PublicApiSpecController extends AbstractApiSpecController
{
    private const API_GROUP_WHITELIST = ['public'];

    public function __construct(ApiSpecLoadUseCase $useCase)
    {
        parent::__construct($useCase, self::API_GROUP_WHITELIST);
    }

    public function execute(Request $request): Response
    {
        return $this->createResponse($request, 'public');
    }
}
