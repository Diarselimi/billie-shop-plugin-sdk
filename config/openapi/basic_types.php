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
 *     enum=\App\DomainModel\Order\OrderEntity::ALL_STATES,
 *     example="created"
 * )
 */

/**
 * @OA\Schema(
 *     schema="OrderStateV2",
 *     title="Order State",
 *     type="string",
 *     enum=\App\DomainModel\Order\OrderEntity::ALL_STATES_V2,
 *     example="created"
 * )
 */

/**
 * @OA\Schema(
 *     schema="OnboardingState",
 *     title="Onboarding State",
 *     type="string",
 *     example="new",
 *     enum=\App\DomainModel\MerchantOnboarding\MerchantOnboardingEntity::ALL_STATES
 * )
 */

/**
 * @OA\Schema(
 *     schema="OnboardingStateTransition",
 *     title="Onboarding State",
 *     type="string",
 *     example="complete",
 *     enum=\App\DomainModel\MerchantOnboarding\MerchantOnboardingTransitionEntity::ALL_TRANSITIONS
 * )
 */

/**
 * @OA\Schema(
 *     schema="OnboardingStepState",
 *     title="Onboarding Step State",
 *     type="string",
 *     example="new",
 *     enum=\App\DomainModel\MerchantOnboarding\MerchantOnboardingStepEntity::ALL_STATES
 * )
 */

/**
 * @OA\Schema(
 *     schema="OnboardingStepName",
 *     title="Onboarding Step Name",
 *     type="string",
 *     example="financial_assessment",
 *     enum=\App\DomainModel\MerchantOnboarding\MerchantOnboardingStepEntity::ALL_PUBLIC_STEPS
 * )
 */

/**
 * @OA\Schema(
 *     schema="OnboardingStepTransition",
 *     title="Merchant User Onboarding steps transitions",
 *     type="string",
 *     example="complete",
 *     enum=\App\DomainModel\MerchantOnboarding\MerchantOnboardingStepTransitionEntity::ALL_TRANSITIONS
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
 *     minLength=5,
 *     pattern="^[0-9]{5}$",
 *     example="10969"
 * )
 */

/**
 * @OA\Schema(
 *     schema="CountryCode",
 *     title="Country Code",
 *     type="string",
 *     minLength=2,
 *     maxLength=2,
 *     description="ISO 3166-1 alpha-2 country code (two letters: DE, AT, etc.). See https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2",
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
 *     schema="LegalForm",
 *     title="Legal Form",
 *     description="One of the legal form codes available in the `GET /legal-forms` API endpoint.",
 *     type="string",
 *     example="10001",
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

/**
 * @OA\Schema(
 *     schema="DebtorInformationChangeRequestState",
 *     title="Debtor Information ChangeRequest State",
 *     type="string",
 *     example="new",
 *     enum=\App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestEntity::ALL_STATES
 * )
 */

/**
 * @OA\Schema(schema="AmountDTO", title="object <net, gross, tax>",
 *     required={"net", "gross", "tax"},
 *     description="The amount object with split values for net, gross and tax",
 *     properties={
 *          @OA\Property(property="gross", minimum=0.01, type="number", format="float", example=260.27, description="Value greater than 0, with max. 2 decimals. It should equal to net + tax."),
 *          @OA\Property(property="net", minimum=0.01, type="number", format="float", example=200.12, description="Value greater than 0, with max. 2 decimals."),
 *          @OA\Property(property="tax", minimum=0, type="number", format="float", example=60.15, description="Value greater than or equal to 0, with max. 2 decimals."),
 *     }
 * )
 */

/**
 * @OA\Schema(schema="NullableAmountDTO", title="object <net, gross, tax>",
 *     required={"net", "gross", "tax"},
 *     description="The amount object with split values for net, gross and tax",
 *     properties={
 *          @OA\Property(property="gross", minimum=0, type="number", format="float", example=260.27, description="Value greater than or equal to 0, with max. 2 decimals. It should equal to net + tax."),
 *          @OA\Property(property="net", minimum=0, type="number", format="float", example=200.12, description="Value greater than or equal to 0, with max. 2 decimals."),
 *          @OA\Property(property="tax", minimum=0, type="number", format="float", example=60.15, description="Value greater than or equal to 0, with max. 2 decimals."),
 *     }
 * )
 */

/**
 * @OA\Schema(schema="AddressStrictPostalCode", title="Address data are all required except house number.",
 *     required={"street", "postal_code", "city", "country"},
 *     properties={
 *          @OA\Property(property="house_number", ref="#/components/schemas/TinyText", description="House number. Leave empty if it is provided as part of the street", example="45"),
 *          @OA\Property(property="street", ref="#/components/schemas/TinyText", description="Street can be with house number together or provide house number in the dedicated field.", example="Charlottenstr. 45"),
 *          @OA\Property(property="postal_code", ref="#/components/schemas/PostalCode", description="ZIP or postal code.", example="94111"),
 *          @OA\Property(property="city", ref="#/components/schemas/TinyText", example="Berlin", description="City, district, suburb, town, or village.", example="Berlin"),
 *          @OA\Property(property="country", ref="#/components/schemas/CountryCode", description="Two-letter country code", example="DE"),
 *     }
 * )
 */

/**
 * @OA\Schema(schema="Address", title="Address data are all required except house number.",
 *     required={"street", "postal_code", "city", "country"},
 *     properties={
 *          @OA\Property(property="house_number", ref="#/components/schemas/TinyText", description="House number. Leave empty if it is provided as part of the street", example="45"),
 *          @OA\Property(property="street", ref="#/components/schemas/TinyText", description="Street can be with house number together or provide house number in the dedicated field.", example="Charlottenstr. 45"),
 *          @OA\Property(property="postal_code", ref="#/components/schemas/TinyText", description="ZIP or postal code.", example="94111"),
 *          @OA\Property(property="city", ref="#/components/schemas/TinyText", example="Berlin", description="City, district, suburb, town, or village.", example="Berlin"),
 *          @OA\Property(property="country", ref="#/components/schemas/CountryCode", description="Two-letter country code", example="DE"),
 *     }
 * )
 */

/**
 * @OA\Schema(
 *     schema="CheckoutConfirmDebtorCompanyRequest",
 *     title="Checkout Confirm Debtor Company Request",
 *     required={"name", "company_address"},
 *     properties={
 *          @OA\Property(property="name", ref="#/components/schemas/TinyText", example="Billie GmbH"),
 *          @OA\Property(property="company_address", ref="#/components/schemas/AddressStrictPostalCode")
 *     }
 * )
 */

/**
 * @OA\Schema(schema="CheckoutConfirmOrderRequest", required={"amount", "duration", "debtor_company"}, properties={
 *      @OA\Property(property="amount", ref="#/components/schemas/AmountDTO"),
 *      @OA\Property(property="duration", ref="#/components/schemas/OrderDuration"),
 *      @OA\Property(property="debtor", ref="#/components/schemas/CheckoutConfirmDebtorCompanyRequest"),
 *      @OA\Property(property="delivery_address", ref="#/components/schemas/Address"),
 *      @OA\Property(property="external_code", ref="#/components/schemas/TinyText", description="Order external code", example="DE123456-1")
 * })
 */

/**
 * @OA\Schema(
 *     schema="CreateOrderDebtorCompanyRequest",
 *     title="Debtor Company",
 *     required={
 *          "merchant_customer_id", "name", "legal_form", "company_address"
 *     },
 *     properties={
 *          @OA\Property(property="merchant_customer_id", ref="#/components/schemas/TinyText", example="128483", description="Unique identifier of the customer provided by the merchant side."),
 *          @OA\Property(property="name", ref="#/components/schemas/TinyText", example="Billie GmbH"),
 *          @OA\Property(property="company_address", ref="#/components/schemas/AddressStrictPostalCode"),
 *          @OA\Property(property="billing_address", ref="#/components/schemas/Address"),
 *          @OA\Property(property="tax_id", ref="#/components/schemas/TinyText", example="DE1234556"),
 *          @OA\Property(property="tax_number", ref="#/components/schemas/TinyText"),
 *          @OA\Property(property="registration_court", ref="#/components/schemas/TinyText"),
 *          @OA\Property(property="registration_number", ref="#/components/schemas/TinyText", example="HRB 1234556"),
 *          @OA\Property(property="industry_sector", ref="#/components/schemas/TinyText", example="C"),
 *          @OA\Property(property="subindustry_sector", ref="#/components/schemas/TinyText"),
 *          @OA\Property(property="employees_number", ref="#/components/schemas/TinyText", example="1-5"),
 *          @OA\Property(property="legal_form", ref="#/components/schemas/LegalForm"),
 *          @OA\Property(property="established_customer", type="boolean")
 *     }
 * )
 */
