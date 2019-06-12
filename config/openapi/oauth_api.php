<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Post
 * (
 *     path="/oauth/token",
 *     operationId="oauth_token_create",
 *     tags={"OAuth API"},
 *     summary="Request a new OAuth Token",
 * @OA\RequestBody(
 *          @OA\MediaType(mediaType="application/x-www-form-urlencoded",
 *          @OA\Schema(ref="#/components/schemas/OauthTokenCreateRequestBodyParams"))
 *     ),
 * @OA\Parameter(in="query", name="response_type", @OA\Schema(type="string", enum={"code", "token"}), required=false, description="Response type. Required for the Auth Code and Implicit grants."),
 * @OA\Parameter(in="query", name="client_id", @OA\Schema(ref="#/components/schemas/UUID"), required=false, description="Client ID. Required for the Auth Code and Implicit grants."),
 * @OA\Parameter(in="query", name="redirect_uri", @OA\Schema(type="string", format="uri"), required=false, description="Client redirect URI. This parameter is optional, but if not sent the user will be redirected to a pre-defined redirect URI. Used for the Auth Code and Implicit grants."),
 * @OA\Parameter(in="query", name="scope", @OA\Schema(type="string"), required=false, description="A space delimited list of scopes. Required for the Auth Code and Implicit grants."),
 * @OA\Parameter(in="query", name="state", @OA\Schema(type="string"), required=false, description="Client-generated CSRF token. This parameter is optional but highly recommended. The OAuth server should return this same value in the final step, so the client can verify it. Used for the Auth Code and Implicit grants."),
 * @OA\Response(
 *          response=200,
 *          description="Successful",
 *          @OA\JsonContent(ref="#/components/schemas/OauthTokenCreateResponseBody")
 *     ),
 * @OA\Response(
 *          response=302,
 *          description="Redirection for Auth Code and Implicit grants",
 *          headers={@OA\Header(header="Location", required=true, description="Redirects to the `redirect_uri` provided location with the following query string parameters, depending on the requested grant type:
- _Authorization code grant_: If the user approves the client they will be redirected from the authorization server to the client's redirect URI with the following parameters in the query string:
`code` and `state` (CSRF token of the original request).
- _Implicit grant_: If the user approves the client they will be redirected from the client back to the authorization server with the following parameters in the query string: `token_type`
(always `Bearer`), `expires_in` (TTL), `access_token` (JWT) and `state` (CSRF token of the original request).
", @OA\Schema(type="string"))}
 *     )
 * )
 */

/**
 * @OA\Get
 * (
 *     path="/oauth/authorization",
 *     operationId="oauth_token_validate",
 *     tags={"OAuth API"},
 *     summary="Validate OAuth Token",
 * @OA\Response(
 *          response=200,
 *          description="Successful",
 *          @OA\JsonContent(
 *                  type="object",
 *                  required={"client_id", "scopes"},
 *                  properties={
 *                      @OA\Property(property="client_id", ref="#/components/schemas/UUID"),
 *                      @OA\Property(property="user_id", ref="#/components/schemas/UUID", nullable=true),
 *                      @OA\Property(property="scopes", type="array", @OA\Items(type="string"), default={})
 *                  }
 *          )
 *     ),
 * @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 * @OA\Response(response=403, ref="#/components/responses/Forbidden"),
 * @OA\Response(response="default", ref="#/components/responses/ServerError")
 * )
 */

/**
 * @OA\Schema(
 *     schema="OauthGrantType",
 *     title="OAuth Grant Type",
 *     type="string",
 *     enum={"client_credentials", "authorization_code", "password", "refresh_token"},
 *     example="password"
 * )
 */

/**
 * @OA\Schema(
 *     schema="OauthClientCredentialsGrantRequestBodyParams",
 *     title="Client Credentials Params",
 *     type="object",
 *     required={"grant_type", "client_id", "client_secret", "scope"},
 *     properties={
 *        @OA\Property(property="grant_type", example="client_credentials", ref="#/components/schemas/OauthGrantType"),
 *        @OA\Property(property="client_id", ref="#/components/schemas/UUID"),
 *        @OA\Property(property="scope", type="string", description="Space-delimited list of requested scope permissions")
 *     }
 * )
 */

/**
 * @OA\Schema(
 *     schema="OauthClientCredentialsGrantResponseBody",
 *     title="Client Credentials Response",
 *     type="object",
 *     required={"token_type", "expires_in", "access_token"},
 *     properties={
 *        @OA\Property(property="token_type", example="Bearer", type="string"),
 *        @OA\Property(property="expires_in", type="integer", description="TTL of the access token"),
 *        @OA\Property(property="access_token", type="string", description="JWT signed with the authorization server's private key")
 *     }
 * )
 */

/**
 * @OA\Schema(
 *     schema="OauthAuthCodeGrantPartTwoRequestBodyParams",
 *     title="Auth Code Grant (Part Two) Params",
 *     type="object",
 *     required={"grant_type", "client_id", "client_secret", "scope"},
 *     properties={
 *        @OA\Property(property="grant_type", example="authorization_code", ref="#/components/schemas/OauthGrantType"),
 *        @OA\Property(property="client_id", ref="#/components/schemas/UUID"),
 *        @OA\Property(property="client_secret", ref="#/components/schemas/UUID"),
 *        @OA\Property(property="redirect_uri", type="string", format="uri", description="same redirect URI the user was redirected back to"),
 *        @OA\Property(property="code", type="string", description="authorization code got from the query string"),
 *     }
 * )
 */

/**
 * @OA\Schema(
 *     schema="OauthTokenCreateRequestBodyParams",
 *     title="OAuth Token Create Request Body Params",
 *     type="object",
 *     anyOf={
 *       @OA\Schema(ref="#/components/schemas/OauthClientCredentialsGrantRequestBodyParams"),
 *       @OA\Schema(ref="#/components/schemas/OauthAuthCodeGrantPartTwoRequestBodyParams")
 *     }
 * )
 */

/**
 * @OA\Schema(
 *     schema="OauthTokenCreateResponseBody",
 *     title="OAuth Token Create Response Body",
 *     type="object",
 *     anyOf={
 *       @OA\Schema(ref="#/components/schemas/OauthClientCredentialsGrantResponseBody")
 *     }
 * )
 */
