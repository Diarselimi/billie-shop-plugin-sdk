<?php

namespace App\Infrastructure\Repository\MerchantOnboarding;

use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepEntity;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepEntityFactory;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepRepositoryInterface;
use Billie\PdoBundle\DomainModel\StatefulEntity\StatefulEntityRepositoryInterface;
use Billie\PdoBundle\DomainModel\StatefulEntity\StatefulEntityRepositoryTrait;
use Billie\PdoBundle\Infrastructure\Pdo\AbstractPdoRepository;

class MerchantOnboardingStepRepository extends AbstractPdoRepository implements
    MerchantOnboardingStepRepositoryInterface,
    StatefulEntityRepositoryInterface
{
    use StatefulEntityRepositoryTrait;

    public const TABLE_NAME = 'merchant_onboarding_steps';

    public const SELECT_FIELDS = [
        'id',
        'uuid',
        'merchant_onboarding_id',
        'name',
        'state',
        'is_internal',
        'created_at',
        'updated_at',
    ];

    private $factory;

    public function __construct(MerchantOnboardingStepEntityFactory $factory)
    {
        $this->factory = $factory;
    }

    public function insert(MerchantOnboardingStepEntity $entity): void
    {
        $data = [
            'uuid' => $entity->getUuid(),
            'merchant_onboarding_id' => $entity->getMerchantOnboardingId(),
            'name' => $entity->getName(),
            'state' => $entity->getState(),
            'is_internal' => (int) $entity->isInternal(),
            'created_at' => $entity->getCreatedAt()->format(self::DATE_FORMAT),
            'updated_at' => $entity->getUpdatedAt()->format(self::DATE_FORMAT),
        ];
        $sql = $this->generateInsertQuery(self::TABLE_NAME, array_keys($data));
        $id = $this->doInsert($sql, $data);
        $entity->setId($id);
        $this->dispatchCreatedEvent($entity);
    }

    public function findByMerchantOnboardingId(int $merchantOnboardingId): array
    {
        $query = $this->generateSelectQuery(self::TABLE_NAME, self::SELECT_FIELDS) . ' ' .
            "WHERE merchant_onboarding_id = :merchant_onboarding_id AND is_internal=0 ORDER BY id";
        $params = ['merchant_onboarding_id' => $merchantOnboardingId];
        $rows = $this->doFetchAll($query, $params);

        return $this->factory->createFromArrayCollection($rows);
    }
}
