<?php

namespace App\Application\UseCase\CreateOrder\Request;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="CreateOrderDebtorCompanyRequestV2",
 *     title="Debtor Company",
 *     required={
 *          "merchant_customer_id", "name", "legal_form", "address_street",
 *          "address_city", "address_postal_code", "address_country"
 *     },
 *     properties={
 *          @OA\Property(property="merchant_customer_id", ref="#/components/schemas/TinyText", example="128483", description="Unique identifier of the customer provided by the merchant side."),
 *          @OA\Property(property="name", ref="#/components/schemas/TinyText", example="Billie GmbH"),
 *          @OA\Property(property="address", ref="#/components/schemas/Address"),
 *          @OA\Property(property="tax_id", ref="#/components/schemas/TinyText", example="DE1234556", nullable=true),
 *          @OA\Property(property="tax_number", ref="#/components/schemas/TinyText", nullable=true),
 *          @OA\Property(property="registration_court", ref="#/components/schemas/TinyText", nullable=true),
 *          @OA\Property(property="registration_number", ref="#/components/schemas/TinyText", example="HRB 1234556", nullable=true),
 *          @OA\Property(property="industry_sector", ref="#/components/schemas/TinyText", example="C", nullable=true),
 *          @OA\Property(property="subindustry_sector", ref="#/components/schemas/TinyText", nullable=true),
 *          @OA\Property(property="employees_number", ref="#/components/schemas/TinyText", example="1-5", nullable=true),
 *          @OA\Property(property="legal_form", ref="#/components/schemas/TinyText", example="10001", description="One of the legal form codes available in the `GET /legal-forms` API endpoint."),
 *          @OA\Property(property="established_customer", type="boolean", nullable=true)
 *     }
 * )
 */
class CreateOrderDebtorCompanyRequestV2
{
}
