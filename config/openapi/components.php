<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Components(
 *     securitySchemes={
 *         @OA\SecurityScheme(securityScheme="oauth2", type="oauth2", flows={@OA\Flow(flow="password", tokenUrl="/oauth/token", scopes={"default":"default"})}),
 *         @OA\SecurityScheme(securityScheme="apiKey", type="apiKey", in="header", name="X-Api-Key", description="Deprecated. Please use OAuth 2.0.")
 *     },
 *     responses={
 *          @OA\Response(response="ServerError", description="Unexpected Server Error", @OA\JsonContent(ref="#/components/schemas/AbstractErrorObject")),
 *          @OA\Response(response="NotFound", description="Resource Not Found", @OA\JsonContent(ref="#/components/schemas/AbstractErrorObject")),
 *          @OA\Response(response="Unauthorized", description="Unauthorized request or invalid credentials", @OA\JsonContent(ref="#/components/schemas/AbstractErrorObject")),
 *          @OA\Response(response="Forbidden", description="Forbidden Request. The operation cannot be completed.", @OA\JsonContent(ref="#/components/schemas/AbstractErrorObject")),
 *          @OA\Response(response="BadRequest", description="Invalid Request Data", @OA\JsonContent(ref="#/components/schemas/ValidationErrorsObject")),
 *     }
 * )
 */
