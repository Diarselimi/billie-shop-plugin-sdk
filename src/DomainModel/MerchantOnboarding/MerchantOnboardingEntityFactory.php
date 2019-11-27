<?php

namespace App\DomainModel\MerchantOnboarding;

use App\Helper\Uuid\UuidGeneratorInterface;
use App\Support\AbstractFactory;

class MerchantOnboardingEntityFactory extends AbstractFactory
{
    private $uuidGenerator;

    public function __construct(UuidGeneratorInterface $uuidGenerator)
    {
        $this->uuidGenerator = $uuidGenerator;
    }

    public function createFromArray(array $data): MerchantOnboardingEntity
    {
        return (new MerchantOnboardingEntity())
            ->setId($data['id'])
            ->setUuid($data['uuid'])
            ->setState($data['state'])
            ->setMerchantId($data['merchant_id'])
            ->setCreatedAt(new \DateTime($data['created_at']))
            ->setUpdatedAt(new \DateTime($data['updated_at']));
    }

    public function create(string $state, int $merchantId): MerchantOnboardingEntity
    {
        return (new MerchantOnboardingEntity())
            ->setUuid($this->uuidGenerator->uuid4())
            ->setState($state)
            ->setMerchantId($merchantId)
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime());
    }
}
