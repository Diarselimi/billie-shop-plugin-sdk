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

    public function findNewestByPaymentUuid(string $paymentUuid): ?MerchantOnboardingEntity
    {
        $fields = array_map(
            static function (string $field) {
                return self::TABLE_NAME . '.' . $field;
            },
            self::SELECT_FIELDS
        );

        $table = self::TABLE_NAME;
        $query = $this->generateSelectQuery($table, $fields)
            . " INNER JOIN merchants ON merchants.id = {$table}.merchant_id 
                WHERE merchants.payment_merchant_id = :merchant_payment_uuid 
                ORDER BY {$table}.id DESC LIMIT 1"
        ;

        $row = $this->doFetchOne($query, ['merchant_payment_uuid' => $paymentUuid]);

        return $row ? $this->factory->createFromArray($row) : null;
    }

    public function update(MerchantOnboardingEntity $entity): void
    {
        $entity->setUpdatedAt(new \DateTime());

        $this->doUpdate('
            UPDATE ' . self::TABLE_NAME . '
            SET state = :state, updated_at = :updated_at
            WHERE id = :id
        ', [
            'state' => $entity->getState(),
            'id' => $entity->getId(),
            'updated_at' => $entity->getUpdatedAt()->format(self::DATE_FORMAT),
        ]);
    }
}
