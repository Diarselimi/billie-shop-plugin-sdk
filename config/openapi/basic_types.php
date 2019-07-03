<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="UUID",
 *     type="string",
 *     minLength=36,
 *     maxLength=36,
 *     format="uuid",
 *     example="12345667-890a-bcde-f123-34567890abcd"
 * )
 */

/**
 * @OA\Schema(
 *     schema="OrderState",
 *     title="Order State",
 *     type="string",
 *     enum=\App\DomainModel\Order\OrderStateManager::ALL_STATES,
 *     example="created"
 * )
 */

/**
 * @OA\Schema(
 *     schema="OrderDeclineReason",
 *     title="Order Decline Reason",
 *     type="string",
 *     enum=\App\DomainModel\Order\OrderDeclinedReasonsMapper::REASONS,
 *     example="debtor_not_identified"
 * )
 */

/**
 * @OA\Schema(
 *     schema="PublicApiGroup",
 *     title="API Group",
 *     type="string",
 *     enum=\App\Http\Controller\PublicApi\PublicApiSpecController::API_GROUP_WHITELIST,
 *     example="standard"
 * )
 */

/**
 * @OA\Schema(
 *     schema="PrivateApiGroup",
 *     title="API Group",
 *     type="string",
 *     enum=\App\Http\Controller\PrivateApi\PrivateApiSpecController::API_GROUP_WHITELIST,
 *     example="standard"
 * )
 */

/**
 * @OA\Schema(
 *     schema="OrderDunningStatus",
 *     title="Order Dunning Status",
 *     type="string",
 *     nullable=true,
 *     enum={"not_started", "active", "paused", "inactive"},
 *     example="active"
 * )
 */

/**
 * @OA\Schema(
 *     schema="OrderDuration",
 *     title="Order Duration",
 *     type="integer",
 *     minimum=\App\Application\Validator\Constraint\OrderDuration::DURATION_MIN,
 *     maximum=\App\Application\Validator\Constraint\OrderDuration::DURATION_MAX,
 *     example=30
 * )
 */

/**
 * @OA\Schema(
 *     schema="PostalCode",
 *     title="Postal Code",
 *     type="string",
 *     maxLength=5,
 *     example="10969"
 * )
 */

/**
 * @OA\Schema(
 *     schema="CountryCode",
 *     title="Country Code",
 *     type="string",
 *     maxLength=2,
 *     pattern="^[A-Za-z]{2}$",
 *     example="DE"
 * )
 */

/**
 * @OA\Schema(
 *     schema="PhoneNumber",
 *     title="Phone Number",
 *     type="string",
 *     maxLength=2,
 *     pattern="^(\+|\d|\()[ \-\/0-9()]{5,20}$",
 *     example="030 31199251"
 * )
 */

/**
 * @OA\Schema(
 *     schema="DateTime",
 *     title="Date-Time",
 *     type="string",
 *     format="date-time",
 *     example="2019-01-20 14:00:00"
 * )
 */

/**
 * @OA\Schema(
 *     schema="TinyText",
 *     title="string",
 *     type="string",
 *     maxLength=255
 * )
 */
