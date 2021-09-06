<?php

namespace App\Application\UseCase\CreateMerchantWithCompany;

use App\Application\UseCase\ValidatedRequestInterface;
use App\DomainModel\Merchant\MerchantWithCompanyCreationDTO;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @OA\Schema(
 *      schema="CreateMerchantWithCompanyRequest",
 *      x={"groups": {"private"}},
 *      type="object",
 *      properties={
 *          @OA\Property(property="name", ref="#/components/schemas/TinyText"),
 *          @OA\Property(property="legal_form", ref="#/components/schemas/LegalForm"),
 *          @OA\Property(property="address_street", ref="#/components/schemas/TinyText", example="Charlotten Str."),
 *          @OA\Property(property="address_house", ref="#/components/schemas/TinyText", example="4"),
 *          @OA\Property(property="address_city", ref="#/components/schemas/TinyText", example="Berlin"),
 *          @OA\Property(property="address_postal_code", ref="#/components/schemas/PostalCode"),
 *          @OA\Property(property="address_country", ref="#/components/schemas/CountryCode"),
 *          @OA\Property(property="crefo_id", ref="#/components/schemas/TinyText"),
 *          @OA\Property(property="schufa_id", ref="#/components/schemas/TinyText"),
 *          @OA\Property(property="tax_id", ref="#/components/schemas/TinyText"),
 *          @OA\Property(property="registration_number", ref="#/components/schemas/TinyText"),
 *          @OA\Property(property="merchant_financing_limit", type="number", format="float"),
 *          @OA\Property(property="initial_debtor_financing_limit", type="number", format="float"),
 *          @OA\Property(property="webhook_url", type="string", format="uri"),
 *          @OA\Property(property="webhook_authorization", type="string", example="X-Api-Key: test",
 *               description="Authorization header that will be sent with the merchant webhooks.
 *                             Currently only `X-Api-Key: XXX` and `Authorization: Basic XXX` formats are supported."
 *          ),
 *          @OA\Property(property="iban", ref="#/components/schemas/TinyText", example="DE87500105173872482875"),
 *          @OA\Property(property="bic", ref="#/components/schemas/TinyText", example="AABSDE31"),
 *          @OA\Property(property="is_onboarding_complete", type="boolean"),
 *          @OA\Property(property="fee_rates", type="string"),
 *      },
 *      required={"name", "legal_form", "address_street", "address_city", "address_postal_code", "address_country", "crefo_id", "schufa_id"}
 * )
 */
class CreateMerchantWithCompanyRequest extends MerchantWithCompanyCreationDTO implements ValidatedRequestInterface
{
    /**
     * @Assert\NotBlank()
     */
    private ?array $feeRates = null;

    public function getFeeRates(): ?array
    {
        return $this->feeRates;
    }

    public function setFeeRates(?array $feeRates): self
    {
        $this->feeRates = $feeRates;

        return $this;
    }
}
