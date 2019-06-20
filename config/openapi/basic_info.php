<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Info(@OA\License(name="© Billie GmbH, 2019", url="https://www.billie.io/agb/"), termsOfService="https://www.billie.io/agb/")
 */

// Extra endpoints:
/**
 * @OA\Get(
 *     path="/healthcheck",
 *     operationId="api_status",
 *     summary="API Status",
 *     @OA\Response(
 *          response=200,
 *          description="Successful",
 *          content={"text/plain":@OA\MediaType(mediaType="text/plain", @OA\Schema(type="string", default="paella is alive"))}
 *     )
 * )
 */
