<?php

namespace App\Application\UseCase\MerchantOnboardingTransition;

use App\Application\UseCase\ValidatedRequestInterface;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @OA\Schema(
 *      schema="MerchantOnboardingTransitionRequest",
 *      type="object",
 *      properties={
 *          @OA\Property(property="transition", ref="#/components/schemas/OnboardingStateTransition")
 *      }
 * )
 */
class MerchantOnboardingTransitionRequest implements ValidatedRequestInterface
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
    private $transition;

    public function __construct(string $merchantPaymentUuid, $transition)
    {
        $this->merchantPaymentUuid = $merchantPaymentUuid;
        $this->transition = $transition;
    }

    public function getMerchantPaymentUuid(): string
    {
        return $this->merchantPaymentUuid;
    }

    public function getTransition(): string
    {
        return $this->transition;
    }
}
