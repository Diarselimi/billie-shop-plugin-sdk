<?php

namespace App\Application\UseCase\UpdateMerchantOnboardingStepState;

use App\Application\UseCase\ValidatedRequestInterface;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @OA\Schema(
 *      schema="UpdateMerchantOnboardingStepStateRequest",
 *      type="object",
 *      properties={
 *          @OA\Property(property="step", type="string"),
 *          @OA\Property(property="state", type="string")
 *      }
 * )
 */
class UpdateMerchantOnboardingStepStateRequest implements ValidatedRequestInterface
{
    /**
     * @Assert\NotBlank()
     * @Assert\Uuid()
     */
    private $merchantPaymentUuid;

    /**
     * @Assert\NotBlank()
     * @Assert\Type("string")
     */ // TODO: validate choice list, add enum to the OA docs

    private $step;

    /**
     * @Assert\NotBlank()
     * @Assert\Type("string")
     */ // TODO: validate choice list, add enum to the OA docs

    private $state;

    public function __construct(string $merchantPaymentUuid, $step, $state)
    {
        $this->merchantPaymentUuid = $merchantPaymentUuid;
        $this->step = $step;
        $this->state = $state;
    }

    public function getMerchantPaymentUuid(): string
    {
        return $this->merchantPaymentUuid;
    }

    public function getStep(): string
    {
        return $this->step;
    }

    public function getState(): string
    {
        return $this->state;
    }
}
