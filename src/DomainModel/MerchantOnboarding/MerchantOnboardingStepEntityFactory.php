<?php

namespace App\DomainModel\MerchantOnboarding;

use App\Helper\Uuid\UuidGeneratorInterface;
use App\Support\AbstractFactory;

class MerchantOnboardingStepEntityFactory extends AbstractFactory
{
    private $uuidGenerator;

    public function __construct(UuidGeneratorInterface $uuidGenerator)
    {
        $this->uuidGenerator = $uuidGenerator;
    }

    public function createFromArray(array $data): MerchantOnboardingStepEntity
    {
        return (new MerchantOnboardingStepEntity())
            ->setId($data['id'])
            ->setUuid($data['uuid'])
            ->setName($data['name'])
            ->setState($data['state'])
            ->setIsInternal(boolval($data['is_internal']))
            ->setMerchantOnboardingId($data['merchant_onboarding_id'])
            ->setCreatedAt(new \DateTime($data['created_at']))
            ->setUpdatedAt(new \DateTime($data['updated_at']));
    }

    public function create(string $name, string $state, int $merchantOnboardingId): MerchantOnboardingStepEntity
    {
        return (new MerchantOnboardingStepEntity())
            ->setUuid($this->uuidGenerator->uuid4())
            ->setName($name)
            ->setState($state)
            ->setMerchantOnboardingId($merchantOnboardingId)
            ->setIsInternal(false)
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime());
    }
}
