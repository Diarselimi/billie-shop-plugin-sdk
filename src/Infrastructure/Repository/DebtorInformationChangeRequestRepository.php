<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestEntity;
use App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestEntityFactory;
use App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestRepositoryInterface;
use Billie\PdoBundle\Infrastructure\Pdo\AbstractPdoRepository;

class DebtorInformationChangeRequestRepository extends AbstractPdoRepository implements DebtorInformationChangeRequestRepositoryInterface
{
    public const TABLE_NAME = 'debtor_information_change_requests';

    private const SELECT_FIELDS = [
        'id',
        'uuid',
        'company_uuid',
        'name',
        'city',
        'postal_code',
        'street',
        'house_number',
        'merchant_user_id',
        'is_seen',
        'state',
        'created_at',
        'updated_at',
    ];

    private $factory;

    public function __construct(
        DebtorInformationChangeRequestEntityFactory $factory
    ) {
        $this->factory = $factory;
    }

    public function insert(DebtorInformationChangeRequestEntity $entity): void
    {
        $sql = $this->generateInsertQuery(self::TABLE_NAME, self::SELECT_FIELDS);
        $id = $this->doInsert($sql, $entity->toArray());

        $entity->setId($id);
    }

    public function update(DebtorInformationChangeRequestEntity $entity): void
    {
        $entity->setUpdatedAt(new \DateTime());

        $this->doUpdate('
            UPDATE ' . self::TABLE_NAME . '
            SET state = :state, is_seen = :is_seen, updated_at = :updated_at
            WHERE id = :id
        ', [
            'state' => $entity->getState(),
            'id' => $entity->getId(),
            'is_seen' => $entity->isSeen(),
            'updated_at' => $entity->getUpdatedAt()->format(self::DATE_FORMAT),
        ]);
    }

    public function getNotSeenCountByMerchantId(int $merchantId): int
    {
        $total = $this->doFetchOne('
            SELECT COUNT(*) AS total
            FROM ' . self::TABLE_NAME . ' dicr
            INNER JOIN merchant_users mu ON (
                dicr.merchant_user_id = mu.id
                AND mu.merchant_id = :merchantId
            )
            WHERE dicr.is_seen = 0
            AND dicr.state IN(:stateComplete, :stateDeclined)
        ', [
            'merchantId' => $merchantId,
            'stateComplete' => DebtorInformationChangeRequestEntity::STATE_COMPLETE,
            'stateDeclined' => DebtorInformationChangeRequestEntity::STATE_DECLINED,
        ]);

        return (int) $total['total'];
    }

    public function getNotSeenRequestByCompanyUuid(string $companyUuid): ?DebtorInformationChangeRequestEntity
    {
        $row = $this->doFetchOne(
            '
          SELECT ' . implode(', ', self::SELECT_FIELDS) . '
          FROM ' . self::TABLE_NAME . '
          WHERE company_uuid = :companyUuid AND is_seen = 0
          ORDER BY created_at DESC LIMIT 1
        ',
            [
                'companyUuid' => $companyUuid,
            ]
        );

        return $row ? $this->factory->createFromDatabaseRow($row) : null;
    }
}
