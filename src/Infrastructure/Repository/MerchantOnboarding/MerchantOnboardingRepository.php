<?php

namespace App\Infrastructure\Repository\MerchantOnboarding;

use App\DomainModel\MerchantOnboarding\MerchantOnboardingEntity;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingEntityFactory;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingRepositoryInterface;
use Billie\PdoBundle\DomainModel\StatefulEntity\StatefulEntityRepositoryInterface;
use Billie\PdoBundle\DomainModel\StatefulEntity\StatefulEntityRepositoryTrait;
use Billie\PdoBundle\Infrastructure\Pdo\AbstractPdoRepository;

class MerchantOnboardingRepository extends AbstractPdoRepository implements
    MerchantOnboardingRepositoryInterface,
    StatefulEntityRepositoryInterface
{
    use StatefulEntityRepositoryTrait;

    public const TABLE_NAME = 'merchant_onboardings';

    public const SELECT_FIELDS = [
        'id',
        'uuid',
        'merchant_id',
        'state',
        'created_at',
        'updated_at',
    ];

    private $factory;

    public function __construct(MerchantOnboardingEntityFactory $factory)
    {
        $this->factory = $factory;
    }

    public function insert(MerchantOnboardingEntity $entity): void
    {
        $data = [
            'uuid' => $entity->getUuid(),
            'merchant_id' => $entity->getMerchantId(),
            'state' => $entity->getState(),
            'created_at' => $entity->getCreatedAt()->format(self::DATE_FORMAT),
            'updated_at' => $entity->getUpdatedAt()->format(self::DATE_FORMAT),
        ];
        $sql = $this->generateInsertQuery(self::TABLE_NAME, array_keys($data));
        $id = $this->doInsert($sql, $data);
        $entity->setId($id);
        $this->dispatchCreatedEvent($entity);
    }

    public function findNewestByMerchant(int $merchantId): ?MerchantOnboardingEntity
    {
        $query = $this->generateSelectQuery(self::TABLE_NAME, self::SELECT_FIELDS) . ' ' .
            "WHERE merchant_id = :merchant_id ORDER BY id DESC LIMIT 1";
        $params = ['merchant_id' => $merchantId];
        $row = $this->doFetchOne($query, $params);

        return $row ? $this->factory->createFromArray($row) : null;
    }
}
