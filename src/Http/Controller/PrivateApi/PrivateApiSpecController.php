<?php

namespace App\Http\Controller\PrivateApi;

use App\Application\UseCase\ApiSpecLoad\ApiSpecLoadUseCase;
use App\Http\Controller\AbstractApiSpecController;
use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *     path="/internal-docs/{apiGroup}/billie-pad-openapi.yaml",
 *     operationId="docs_internal_get_specs",
 *     summary="Internal API Specs",
 *
 *     tags={"Docs"},
 *     x={"groups":{"support"}},
 *
 *     @OA\Parameter(in="path", name="apiGroup", description="API group name", required=true,
 *          @OA\Schema(ref="#/components/schemas/PrivateApiGroup")
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
class PrivateApiSpecController extends AbstractApiSpecController
{
    public const API_GROUP_WHITELIST = [
        'full',
        'salesforce',
        'support',
        'standard',
        'checkout-server',
        'checkout-client',
        'dashboard',
    ];

    public function __construct(ApiSpecLoadUseCase $useCase)
    {
        parent::__construct($useCase, self::API_GROUP_WHITELIST);
    }
}
