<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Post(
 *     path="/oauth/token",
 *     operationId="oauth_token_create",
 *     tags={"Authentication"},
 *     x={"groups":{"public", "private"}},
 *     summary="Request OAuth Token",
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json",
 *          @OA\Schema(ref="#/components/schemas/OauthClientCredentialsGrantRequestBodyParams"))
 *     ),
 *
 *     @OA\Response(
 *          response=200,
 *          description="Successful",
 *          @OA\JsonContent(ref="#/components/schemas/OauthTokenResponseBody")
 *     )
 * )
 */

/**
 * @OA\Get(
 *     path="/oauth/authorization",
 *     operationId="oauth_token_validate",
 *     tags={"Authentication"},
 *     x={"groups":{"public", "private"}},
 *     summary="Validate OAuth Token",
 *
 *     @OA\Response(
 *          response=200,
 *          description="Successful",
 *          @OA\JsonContent(
 *                  type="object",
 *                  required={"client_id", "scopes"},
 *                  properties={
 *                      @OA\Property(property="client_id", ref="#/components/schemas/UUID"),
 *                      @OA\Property(property="scopes", type="array", @OA\Items(type="string"), default={})
 *                  }
 *          )
 *     ),
 *
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=403, ref="#/components/responses/Forbidden"),
 *     @OA\Response(response="default", ref="#/components/responses/ServerError")
 * )
 */

/**
 * @OA\Schema(
 *     schema="OauthClientCredentialsGrantRequestBodyParams",
 *     title="Request OAuth Token",
 *     type="object",
 *     required={"grant_type", "client_id", "client_secret"},
 *     properties={
 *        @OA\Property(property="grant_type", example="client_credentials", type="string"),
 *        @OA\Property(property="client_id", ref="#/components/schemas/UUID"),
 *        @OA\Property(property="client_secret", type="string")
 *     }
 * )
 */

/**
 * @OA\Schema(
 *     schema="OauthTokenResponseBody",
 *     title="Request Oauth Token Response",
 *     type="object",
 *     properties={
 *        @OA\Property(property="token_type", example="Bearer", type="string", nullable=false),
 *        @OA\Property(
 *          property="expires_in",
 *          type="integer",
 *          description="TTL of the access token in seconds (Default is 8 hours)",
 *          nullable=false
 *        ),
 *        @OA\Property(
 *          property="access_token",
 *          type="string",
 *          description="JWT signed with the authorization server's private key",
 *          nullable=false
 *        )
 *     }
 * )
 */
