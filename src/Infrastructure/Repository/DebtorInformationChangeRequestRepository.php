<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestEntity;
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
            SET state = :state, updated_at = :updated_at
            WHERE id = :id
        ', [
            'state' => $entity->getState(),
            'id' => $entity->getId(),
            'updated_at' => $entity->getUpdatedAt()->format(self::DATE_FORMAT),
        ]);
    }
}
