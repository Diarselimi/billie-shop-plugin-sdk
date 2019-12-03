<?php

namespace App\Application\UseCase\MerchantOnboardingStepTransition;

use App\Application\UseCase\ValidatedRequestInterface;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @OA\Schema(
 *      schema="MerchantOnboardingStepTransitionRequest",
 *      type="object",
 *      properties={
 *          @OA\Property(property="step", ref="#/components/schemas/OnboardingStepName"),
 *          @OA\Property(property="transition", ref="#/components/schemas/OnboardingStepTransition")
 *      }
 * )
 */
class MerchantOnboardingStepTransitionRequest implements ValidatedRequestInterface
{
    /**
     * @Assert\NotBlank()
     * @Assert\Uuid()
     */
    private $merchantPaymentUuid;

    /**
     * @Assert\NotBlank()
     * @Assert\Type("string")
     */
    private $step;

    /**
     * @Assert\NotBlank()
     * @Assert\Type("string")
     */
    private $transition;

    public function __construct(string $merchantPaymentUuid, $step, $state)
    {
        $this->merchantPaymentUuid = $merchantPaymentUuid;
        $this->step = $step;
        $this->transition = $state;
    }

    public function getMerchantPaymentUuid(): string
    {
        return $this->merchantPaymentUuid;
    }

    public function getStep(): string
    {
        return $this->step;
    }

    public function getTransition(): string
    {
        return $this->transition;
    }
}
