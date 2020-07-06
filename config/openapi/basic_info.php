<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Info(@OA\License(name="© Billie GmbH, 2020", url="https://www.billie.io/agb/"), termsOfService="https://www.billie.io/agb/")
 */

// Extra endpoints:
/**
 * @OA\Get(
 *     path="/healthcheck",
 *     operationId="api_status",
 *     summary="API Status",
 *     description="Checks the health of the API server",
 *     tags={"Support"},
 *     x={"groups":{"private"}},
 *     @OA\Response(
 *          response=200,
 *          description="Successful",
 *          content={"text/plain":@OA\MediaType(mediaType="text/plain", @OA\Schema(type="string", default="paella_core is alive"))}
 *     )
 * )
 */
