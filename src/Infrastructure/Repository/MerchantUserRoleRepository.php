<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\MerchantUser\MerchantUserDefaultRoles;
use App\DomainModel\MerchantUser\MerchantUserRoleEntity;
use App\DomainModel\MerchantUser\MerchantUserRoleEntityFactory;
use App\DomainModel\MerchantUser\MerchantUserRoleRepositoryInterface;
use Billie\PdoBundle\Infrastructure\Pdo\AbstractPdoRepository;

class MerchantUserRoleRepository extends AbstractPdoRepository implements MerchantUserRoleRepositoryInterface
{
    public const TABLE_NAME = 'merchant_user_roles';

    public const TABLE_FIELDS = [
        'id',
        'uuid',
        'merchant_id',
        'name',
        'permissions',
        'created_at',
        'updated_at',
    ];

    private $factory;

    public function __construct(MerchantUserRoleEntityFactory $factory)
    {
        $this->factory = $factory;
    }

    public function create(MerchantUserRoleEntity $entity): void
    {
        $id = $this->doInsert(
            '
            INSERT INTO ' . self::TABLE_NAME . ' (`uuid`, `merchant_id`, `name`, `permissions`, `created_at`, `updated_at`)
            VALUES (:uuid, :merchant_id, :role_name, :permissions, :created_at, :updated_at)
            ',
            [
                'uuid' => $entity->getUuid(),
                'merchant_id' => $entity->getMerchantId(),
                'role_name' => $entity->getName(),
                'permissions' => json_encode($entity->getPermissions()),
                'created_at' => $entity->getCreatedAt()->format(self::DATE_FORMAT),
                'updated_at' => $entity->getUpdatedAt()->format(self::DATE_FORMAT),
            ]
        );

        $entity->setId($id);
    }

    private function getOneBy(string $colName, $value, int $merchantId = null): ?MerchantUserRoleEntity
    {
        $query = 'SELECT ' . implode(', ', self::TABLE_FIELDS) .
            ' FROM ' . self::TABLE_NAME .
            ' WHERE ' . $colName . ' = :' . $colName;

        $params = [$colName => $value];

        if ($merchantId !== null) {
            $query .= ' AND merchant_id = :merchant_id';
            $params['merchant_id'] = $merchantId;
        }

        $row = $this->doFetchOne($query, $params);

        if (!$row) {
            return null;
        }

        return $this->factory->createFromDatabaseRow($row);
    }

    public function getOneByUuid(string $uuid, int $merchantId = null): ?MerchantUserRoleEntity
    {
        return $this->getOneBy('uuid', $uuid, $merchantId);
    }

    public function getOneById(int $id, int $merchantId = null): ?MerchantUserRoleEntity
    {
        return $this->getOneBy('id', $id, $merchantId);
    }

    private function findBy(string $colName, $value): \Generator
    {
        $query = 'SELECT ' . implode(', ', self::TABLE_FIELDS) .
            ' FROM ' . self::TABLE_NAME .
            ' WHERE ' . self::TABLE_NAME . '.name != :billie_role_name' .
            ' AND ' . $colName . ' = :' . $colName .
            ' ORDER BY created_at';

        $params = [
            // never show the internal billie role
            'billie_role_name' => MerchantUserDefaultRoles::ROLE_BILLIE_ADMIN['name'],
            $colName => $value,
        ];

        $stmt = $this->doExecute($query, $params);
        $count = 0;

        while ($stmt && $row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            yield $this->factory->createFromDatabaseRow($row);
            $count++;
        }

        if ($count === 0) {
            yield from [];
        }
    }

    public function findAllByMerchantId(int $merchantId): \Generator
    {
        $generator = $this->findBy('merchant_id', $merchantId);

        if (!$generator->valid()) {
            yield from [];

            return;
        }

        foreach ($generator as $value) {
            yield $value;
        }
    }

    public function getOneByName(string $name, int $merchantId): ?MerchantUserRoleEntity
    {
        return $this->getOneBy('name', $name, $merchantId);
    }
}
