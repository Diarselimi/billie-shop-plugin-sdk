<?php

namespace App\Http\Controller\ApiDocs;

use App\Application\UseCase\ApiSpecLoad\ApiSpecLoadUseCase;
use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *     path="/docs/{apiGroup}/billie-pad-openapi.yaml",
 *     operationId="docs_get_specs",
 *     summary="Public API Specs",
 *
 *     tags={"Docs"},
 *     x={"groups":{"support"}},
 *
 *     @OA\Parameter(in="path", name="apiGroup", description="API group name", required=true,
 *          @OA\Schema(ref="#/components/schemas/PublicApiGroup")
 *     ),
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
    public const API_GROUP_WHITELIST = [
        'standard',
        'checkout-server',
    ];

    public function __construct(ApiSpecLoadUseCase $useCase)
    {
        parent::__construct($useCase, self::API_GROUP_WHITELIST);
    }
}
