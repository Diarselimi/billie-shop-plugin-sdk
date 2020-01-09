<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\MerchantUser\MerchantUserEntity;
use App\DomainModel\MerchantUser\MerchantUserEntityFactory;
use App\DomainModel\MerchantUser\MerchantUserRepositoryInterface;
use Billie\PdoBundle\Infrastructure\Pdo\AbstractPdoRepository;

class MerchantUserRepository extends AbstractPdoRepository implements MerchantUserRepositoryInterface
{
    public const TABLE_NAME = 'merchant_users';

    public const SELECT_FIELDS = [
        'id',
        'user_id',
        'merchant_id',
        'signatory_power_uuid',
        'first_name',
        'last_name',
        'role_id',
        'permissions',
        'created_at',
        'updated_at',
    ];

    private $factory;

    public function __construct(MerchantUserEntityFactory $factory)
    {
        $this->factory = $factory;
    }

    public function create(MerchantUserEntity $merchantUserEntity): void
    {
        $id = $this->doInsert(
            '
            INSERT INTO ' . self::TABLE_NAME . ' (`user_id`, `merchant_id`, `role_id`, `first_name`, `last_name`, `permissions`, `created_at`, `updated_at`)
            VALUES (:user_id, :merchant_id, :role_id, :first_name, :last_name, :permissions, :created_at, :updated_at)
            ',
            [
                'user_id' => $merchantUserEntity->getUuid(),
                'merchant_id' => $merchantUserEntity->getMerchantId(),
                'role_id' => $merchantUserEntity->getRoleId(),
                'first_name' => $merchantUserEntity->getFirstName(),
                'last_name' => $merchantUserEntity->getLastName(),
                'permissions' => $merchantUserEntity->getPermissions() ? json_encode($merchantUserEntity->getPermissions()) : null,
                'created_at' => $merchantUserEntity->getCreatedAt()->format(self::DATE_FORMAT),
                'updated_at' => $merchantUserEntity->getUpdatedAt()->format(self::DATE_FORMAT),
            ]
        );

        $merchantUserEntity->setId($id);
    }

    private function getOneBy(string $colName, $value, int $merchantId = null): ?MerchantUserEntity
    {
        $query = 'SELECT ' . implode(', ', self::SELECT_FIELDS) .
            ' FROM ' . self::TABLE_NAME .
            ' WHERE ' . $colName . ' = :' . $colName;

        $params = [$colName => $value];

        if ($merchantId !== null) {
            $query .= ' AND merchant_id = :merchant_id';
            $params['merchant_id'] = $merchantId;
        }

        $row = $this->doFetchOne($query, $params);

        return $row ? $this->factory->createFromDatabaseRow($row) : null;
    }

    public function getOneByUuid(string $uuid): ?MerchantUserEntity
    {
        return $this->getOneBy('user_id', $uuid);
    }

    public function assignSignatoryPowerToUser(int $id, string $signatoryPowerUuid): void
    {
        $this->update($id, ['signatory_power_uuid' => $signatoryPowerUuid]);
    }

    public function assignIdentityVerificationCaseToUser(int $id, string $identityVerificationCaseUuid): void
    {
        $this->update($id, ['identity_verification_case_uuid' => $identityVerificationCaseUuid]);
    }

    private function update(int $id, array $columnValuePairs): void
    {
        $sql = $this->generateUpdateQuery(self::TABLE_NAME, array_keys($columnValuePairs)). ' WHERE id = :id';
        $this->doExecute($sql, array_merge(['id' => $id], $columnValuePairs));
    }
}
