<?php

namespace App\Application\UseCase\SignatoryPowersSelection;

use App\Application\UseCase\ValidatedRequestInterface;
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
     */
    private $signatoryPowers;

    private $companyId;

    public function __construct(string $companyId, SignatoryPowerDTO ...$signatoryPowers)
    {
        $this->signatoryPowers = $signatoryPowers;
        $this->companyId = $companyId;
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
}
