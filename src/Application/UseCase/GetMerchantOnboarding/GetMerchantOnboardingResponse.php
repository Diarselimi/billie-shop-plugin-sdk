<?php

namespace App\Application\UseCase\GetMerchantOnboarding;

use App\DomainModel\ArrayableInterface;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepEntity;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *      schema="GetMerchantOnboardingResponse",
 *      title="GetMerchantOnboardingResponse",
 *      x={"groups": {"private"}},
 *      type="object",
 *      properties={
 *          @OA\Property(property="onboarding_state", ref="#/components/schemas/OnboardingState"),
 *          @OA\Property(property="onboarding_steps", type="array", @OA\Items(
 *              type="object",
 *              properties={
 *                  @OA\Property(property="uuid", ref="#/components/schemas/UUID"),
 *                  @OA\Property(property="name", ref="#/components/schemas/OnboardingStepName"),
 *                  @OA\Property(property="state", ref="#/components/schemas/OnboardingStepState")
 *              }
 *          )),
 *      }
 * )
 */
class GetMerchantOnboardingResponse implements ArrayableInterface
{
    private $state;

    private $onboardingSteps;

    public function __construct(string $state, MerchantOnboardingStepEntity ...$onboardingSteps)
    {
        $this->state = $state;
        $this->onboardingSteps = $onboardingSteps;
    }

    public function toArray(): array
    {
        return [
            'onboarding_state' => $this->state,
            'onboarding_steps' => array_map(function (MerchantOnboardingStepEntity $entity) {
                return [
                    'uuid' => $entity->getUuid(),
                    'name' => $entity->getName(),
                    'state' => $entity->getState(),
                ];
            }, $this->onboardingSteps),
        ];
    }
}
