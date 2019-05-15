<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\MerchantUser\MerchantUserEntity;
use App\DomainModel\MerchantUser\MerchantUserEntityFactory;
use App\DomainModel\MerchantUser\MerchantUserRepositoryInterface;
use Billie\PdoBundle\Infrastructure\Pdo\AbstractPdoRepository;

class MerchantUserRepository extends AbstractPdoRepository implements MerchantUserRepositoryInterface
{
    public const TABLE_NAME = 'merchant_users';

    private $factory;

    public function __construct(MerchantUserEntityFactory $factory)
    {
        $this->factory = $factory;
    }

    public function create(MerchantUserEntity $merchantUserEntity): void
    {
        $id = $this->doInsert(
            '
            INSERT INTO ' . self::TABLE_NAME . ' (`user_id`, `merchant_id`, `roles`, `created_at`, `updated_at`)
            VALUES (:user_id, :merchant_id, :roles, :created_at, :updated_at)
            ',
            [
                'user_id' => $merchantUserEntity->getUserId(),
                'merchant_id' => $merchantUserEntity->getMerchantId(),
                'roles' => json_encode($merchantUserEntity->getRoles()),
                'created_at' => $merchantUserEntity->getCreatedAt()->format(self::DATE_FORMAT),
                'updated_at' => $merchantUserEntity->getUpdatedAt()->format(self::DATE_FORMAT),
            ]
        );

        $merchantUserEntity->setId($id);
    }

    public function getOneByUserId(string $userId): ? MerchantUserEntity
    {
        $row = $this->doFetchOne(
            'SELECT * FROM ' . self::TABLE_NAME . ' WHERE user_id = :user_id',
            ['user_id' => $userId]
        );

        return $row ? $this->factory->createFromDatabaseRow($row) : null;
    }
}
