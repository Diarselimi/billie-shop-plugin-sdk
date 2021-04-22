<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Components(
 *     securitySchemes={
 *         @OA\SecurityScheme(securityScheme="oauth2", type="oauth2", flows={@OA\Flow(flow="password", tokenUrl="/oauth/token", scopes={"default":"default"})}),
 *         @OA\SecurityScheme(securityScheme="apiKey", type="apiKey", in="header", name="X-Api-Key", description="Deprecated. Please use OAuth 2.0.", x={"groups":{"internal"}})
 *     },
 *     responses={
 *          @OA\Response(response="HtmlDocument", description="HTML Document", @OA\MediaType(mediaType="text/html", @OA\Schema(type="string"))),
 *          @OA\Response(response="YamlDocument", description="YAML Document", @OA\MediaType(mediaType="text/x-yaml", @OA\Schema(type="string"))),
 *          @OA\Response(response="ServerError", description="Unexpected Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorsObject")),
 *          @OA\Response(response="NotFound", description="Resource Not Found", @OA\JsonContent(ref="#/components/schemas/ErrorsObject")),
 *          @OA\Response(response="Unauthorized", description="Unauthorized request or invalid credentials", @OA\JsonContent(ref="#/components/schemas/ErrorsObject")),
 *          @OA\Response(response="NotAcceptable", description="Not Acceptable request.", @OA\JsonContent(ref="#/components/schemas/ErrorsObject")),
 *          @OA\Response(response="Forbidden", description="Forbidden Request. The operation cannot be completed.", @OA\JsonContent(ref="#/components/schemas/ErrorsObject")),
 *          @OA\Response(response="BadRequest", description="Invalid Request Data", @OA\JsonContent(ref="#/components/schemas/ErrorsObject")),
 *          @OA\Response(response="ResourceConflict", description="Resource Already Exists", @OA\JsonContent(ref="#/components/schemas/ErrorsObject")),
 *     }
 * )
 */
