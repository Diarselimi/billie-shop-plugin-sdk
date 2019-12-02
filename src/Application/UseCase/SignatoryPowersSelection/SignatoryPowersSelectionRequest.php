<?php

namespace App\Application\UseCase\SignatoryPowersSelection;

use App\Application\UseCase\ValidatedRequestInterface;
use App\Application\Validator\Constraint as PaellaAssert;
use App\DomainModel\SignatoryPowersSelection\SignatoryPowerDTO;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Annotations as OA;

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

    private $merchantUserId;

    public function __construct(int $merchantUserId, string $companyId, SignatoryPowerDTO ...$signatoryPowers)
    {
        $this->merchantUserId = $merchantUserId;
        $this->companyId = $companyId;
        $this->signatoryPowers = $signatoryPowers;
    }

    public function getCompanyId(): string
    {
        return $this->companyId;
    }

    /**
     * @return SignatoryPowerDTO[]
     */
    public function getSignatoryPowers(): array
    {
        return $this->signatoryPowers;
    }

    public function getMerchantUserId(): int
    {
        return $this->merchantUserId;
    }

    public function findSelectedAsLoggedInSignatory(): ?SignatoryPowerDTO
    {
        foreach ($this->getSignatoryPowers() as $signatoryPowerDTO) {
            if ($signatoryPowerDTO->isIdentifiedAsUser()) {
                return $signatoryPowerDTO;
            }
        }

        return null;
    }
}
