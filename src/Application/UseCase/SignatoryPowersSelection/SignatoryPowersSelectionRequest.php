<?php

namespace App\Application\UseCase\SignatoryPowersSelection;

use App\Application\UseCase\ValidatedRequestInterface;
use App\Application\Validator\Constraint as PaellaAssert;
use App\DomainModel\MerchantUser\MerchantUserEntity;
use App\DomainModel\SignatoryPower\SignatoryPowerSelectionDTO;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @OA\Schema(
 *     schema="SignatoryPowersSelectionRequest",
 *     title="Signatory powers selection request",
 *     properties={
 *      @OA\Property(
 *          property="signatory_powers",
 *          type="array",
 *          nullable=false,
 *          @OA\Items(ref="#/components/schemas/SignatoryPowerDTO")
 *     )
 *  }
 * )
 */
class SignatoryPowersSelectionRequest implements ValidatedRequestInterface
{
    /**
     * @Assert\Valid()
     * @Assert\NotBlank(message="At least one signatory should exist in request.")
     * @PaellaAssert\SignatoryPowersIdentifiedUser
     */
    private $signatoryPowers;

    private $companyId;

    private $merchantUser;

    private $merchantPaymentUuid;

    public function __construct(MerchantUserEntity $merchantUser, string $companyId, string $merchantPaymentUuid, SignatoryPowerSelectionDTO ...$signatoryPowers)
    {
        $this->merchantUser = $merchantUser;
        $this->companyId = $companyId;
        $this->merchantPaymentUuid = $merchantPaymentUuid;
        $this->signatoryPowers = $signatoryPowers;
    }

    public function getCompanyId(): string
    {
        return $this->companyId;
    }

    /**
     * @return SignatoryPowerSelectionDTO[]
     */
    public function getSignatoryPowers(): array
    {
        return $this->signatoryPowers;
    }

    public function getMerchantUser(): MerchantUserEntity
    {
        return $this->merchantUser;
    }

    public function getMerchantPaymentUuid(): string
    {
        return $this->merchantPaymentUuid;
    }

    public function findSelectedAsLoggedInSignatory(): ?SignatoryPowerSelectionDTO
    {
        foreach ($this->getSignatoryPowers() as $signatoryPowerDTO) {
            if ($signatoryPowerDTO->isIdentifiedAsUser()) {
                return $signatoryPowerDTO;
            }
        }

        return null;
    }
}
