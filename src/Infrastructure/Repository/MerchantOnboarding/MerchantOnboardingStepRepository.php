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

    public function getOneByNameAndMerchant(string $name, string $merchantPaymentUuid): ?MerchantOnboardingStepEntity
    {
        $fields = array_map(
            static function (string $field) {
                return self::TABLE_NAME . '.' . $field;
            },
            self::SELECT_FIELDS
        );

        $query = $this->generateSelectQuery(self::TABLE_NAME, $fields)
            . ' INNER JOIN merchant_onboardings ON merchant_onboardings.id = merchant_onboarding_id
                INNER JOIN merchants ON merchant_onboardings.merchant_id = merchants.id
                WHERE merchants.payment_merchant_id = :merchant_payment_uuid AND ' . self::TABLE_NAME . '.name = :name'
        ;

        $row = $this->doFetchOne($query, [
            'name' => $name,
            'merchant_payment_uuid' => $merchantPaymentUuid,
        ]);

        return $row ? $this->factory->createFromArray($row) : null;
    }

    public function findByMerchantOnboardingId(int $merchantOnboardingId, bool $includeInternalSteps): array
    {
        $query = $this->generateSelectQuery(self::TABLE_NAME, self::SELECT_FIELDS)
            . ' WHERE merchant_onboarding_id = :merchant_onboarding_id'
        ;

        if (!$includeInternalSteps) {
            $query .= ' AND is_internal = 0';
        }

        $query .= ' ORDER BY id';

        $rows = $this->doFetchAll($query, ['merchant_onboarding_id' => $merchantOnboardingId]);

        return $this->factory->createFromArrayCollection($rows);
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

    public function update(MerchantOnboardingStepEntity $entity): void
    {
        $this->doUpdate('
            UPDATE ' . self::TABLE_NAME . '
            SET state = :state
            WHERE id = :id
        ', [
            'state' => $entity->getState(),
            'id' => $entity->getId(),
        ]);
    }
}
