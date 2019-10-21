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
 *     minLength=5,
 *     maxLength=21,
 *     pattern="^(\+|\d|\()[ \-\/0-9()]{5,20}$",
 *     example="030 31199251"
 * )
 */

/**
 * @OA\Schema(
 *     schema="IBAN",
 *     title="IBAN",
 *     type="string",
 *     minLength=22,
 *     maxLength=22,
 *     pattern="^[A-Z]{2}(?:[ ]?[0-9]){18,20}$",
 *     example="DE61500105175136458915"
 * )
 */

/**
 * @OA\Schema(
 *     schema="Date",
 *     title="Date",
 *     type="string",
 *     format="date",
 *     example="2019-01-20"
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

/**
 * @OA\Schema(
 *     schema="URL",
 *     title="URL",
 *     type="string",
 *     format="url",
 *     maxLength=255,
 *     example="https://example.com"
 * )
 */

/**
 * @OA\Schema(
 *     schema="Email",
 *     title="Email",
 *     type="string",
 *     format="email",
 *     example="foo@bar.com"
 * )
 */

/**
 * @OA\Schema(
 *     schema="Password",
 *     title="Password",
 *     type="string",
 *     format="password"
 * )
 */

/**
 * @OA\Schema(
 *     schema="Money",
 *     title="Money",
 *     type="number",
 *     format="float",
 *     pattern="^(\d{1,}(.\d{2})?)$",
 *     example=99.95
 * )
 */

/**
 * @OA\Schema(
 *     schema="MerchantUserPermissions",
 *     title="Merchant User Permissions",
 *     type="string",
 *     enum=\App\DomainModel\MerchantUser\MerchantUserPermissions::ALL_PERMISSIONS,
 *     example=\App\DomainModel\MerchantUser\MerchantUserPermissions::ALL_PERMISSIONS
 * )
 */
