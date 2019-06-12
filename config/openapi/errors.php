<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="AbstractErrorObject",
 *     title="Error",
 *     type="object",
 *     oneOf={
 *         @OA\Schema(ref="#/components/schemas/ErrorsObject")
 *     }
 * )
 */

/*
 * OA\Schema(
 *     schema="AbstractErrorObject",
 *     title="Error",
 *     type="object",
 *     description="Paella Core or Paella API error format",
 *     oneOf={
 *         OA\Schema(ref="#/components/schemas/SingleErrorObject"),
 *         OA\Schema(ref="#/components/schemas/ErrorsObject")
 *     }
 * )
 */

/*
 * This is the paella-core error format.
 * TODO: Refactor paella-core errors to make them compatible with paella-api (use ErrorsObject only).
 *
 * OA\Schema(
 *     schema="SingleErrorObject",
 *     title="Single Error (Paella Core format)",
 *     type="object",
 *     required={"error", "code"},
 *     properties={
 *        OA\Property(property="code", type="string", description="Error code"),
 *        OA\Property(property="error", type="string", description="Error message")
 *     }
 * )
 */

/**
 * @OA\Schema(
 *     schema="CheckoutOrderDeclineErrorObject",
 *     title="Checkout Order Decline Error",
 *     type="object",
 *     required={"error", "code"},
 *     properties={
 *        @OA\Property(property="code", type="string", description="Error code"),
 *        @OA\Property(property="error", type="string", description="Error message"),
 *        @OA\Property(
 *          property="reasons",
 *          type="array",
 *          description="Decline reasons",
 *          @OA\Items(ref="#/components/schemas/OrderDeclineReason")
 *       )
 *     }
 * )
 */

/**
 * @OA\Schema(
 *     schema="CheckoutAuthorizeErrorObject",
 *     title="Checkout Authorize Error",
 *     type="object",
 *     oneOf={
 *         @OA\Schema(ref="#/components/schemas/ValidationErrorsObject"),
 *         @OA\Schema(ref="#/components/schemas/CheckoutOrderDeclineErrorObject")
 *     }
 * )
 */

/**
 * @OA\Schema(
 *     schema="ErrorsObject",
 *     title="Multiple Errors",
 *     type="object",
 *     properties={
 *        @OA\Property(property="errors", type="array", @OA\Items(
 *            type="object",
 *            required={"title", "code"},
 *            properties={
 *               @OA\Property(property="title", type="string", description="Error message"),
 *               @OA\Property(property="code", type="string", description="Error code")
 *            }
 *        ))
 *     }
 * )
 *
 */

/**
 * @OA\Schema(
 *     schema="ValidationErrorsObject",
 *     title="Multiple Validation Errors",
 *     type="object",
 *     properties={
 *        @OA\Property(property="errors", type="array", @OA\Items(
 *            type="object",
 *            required={"title", "code"},
 *            properties={
 *               @OA\Property(property="source", type="string", description="Property path where the error occurred."),
 *               @OA\Property(property="title", type="string", description="Error message"),
 *               @OA\Property(property="code", type="string", description="Error code")
 *            }
 *        ))
 *     }
 * )
 */
