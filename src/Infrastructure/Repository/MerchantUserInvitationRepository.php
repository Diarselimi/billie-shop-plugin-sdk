<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\MerchantUser\MerchantUserDefaultRoles;
use App\DomainModel\MerchantUserInvitation\MerchantInvitedUserDTOFactory;
use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationEntity;
use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationEntityFactory;
use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationRepositoryInterface;
use Billie\PdoBundle\Infrastructure\Pdo\AbstractPdoRepository;

class MerchantUserInvitationRepository extends AbstractPdoRepository implements MerchantUserInvitationRepositoryInterface
{
    public const TABLE_NAME = 'merchant_user_invitations';

    public const SELECT_FIELDS = [
        'id',
        'uuid',
        'token',
        'merchant_id',
        'merchant_user_id',
        'merchant_user_role_id',
        'email',
        'created_at',
        'expires_at',
        'revoked_at',
    ];

    private $invitedUserFactory;

    private $factory;

    public function __construct(MerchantUserInvitationEntityFactory $factory, MerchantInvitedUserDTOFactory $invitedUserFactory)
    {
        $this->factory = $factory;
        $this->invitedUserFactory = $invitedUserFactory;
    }

    public function create(MerchantUserInvitationEntity $invitation): void
    {
        $id = $this->doInsert(
            '
            INSERT INTO ' . self::TABLE_NAME . ' (`uuid`, `token`, `merchant_id`, `merchant_user_id`, `merchant_user_role_id`, `email`, `created_at`, `expires_at`)
            VALUES (:uuid , :token, :merchant_id, :merchant_user_id, :merchant_user_role_id, :email, :created_at, :expires_at)
            ',
            [
                'uuid' => $invitation->getUuid(),
                'token' => $invitation->getToken(),
                'merchant_id' => $invitation->getMerchantId(),
                'merchant_user_id' => $invitation->getMerchantUserId(),
                'merchant_user_role_id' => $invitation->getMerchantUserRoleId(),
                'email' => $invitation->getEmail(),
                'created_at' => $invitation->getCreatedAt()->format(self::DATE_FORMAT),
                'expires_at' => $invitation->getExpiresAt()->format(self::DATE_FORMAT),
            ]
        );

        $invitation->setId($id);
    }

    public function revokeValidByEmailAndMerchant(string $email, int $merchantId): void
    {
        $now = (new \DateTime())->format(self::DATE_FORMAT);
        $query = 'UPDATE ' . self::TABLE_NAME .
            " SET revoked_at = :now1 
            WHERE revoked_at IS NULL AND email = :email AND merchant_id = :merchant_id
            AND expires_at > :now2
            ";

        $params = ['email' => $email, 'merchant_id' => $merchantId, 'now1' => $now, 'now2' => $now];

        $this->doExecute($query, $params);
    }

    public function registerToMerchantUser(MerchantUserInvitationEntity $invitation): void
    {
        $now = (new \DateTime())->format(self::DATE_FORMAT);
        $query = '
            UPDATE ' . self::TABLE_NAME . ' 
            SET
                merchant_user_id = :merchant_user_id,
                expires_at = :now
            WHERE id = :id';

        $this->doExecute(
            $query,
            ['id' => $invitation->getId(), 'merchant_user_id' => $invitation->getMerchantUserId(), 'now' => $now]
        );
    }

    public function findByEmailAndMerchant(string $email, int $merchantId, bool $validOnly): ?MerchantUserInvitationEntity
    {
        $query = 'SELECT ' . implode(', ', self::SELECT_FIELDS) .
            ' FROM ' . self::TABLE_NAME .
            " WHERE email = :email AND merchant_id = :merchant_id";
        $params = ['email' => $email, 'merchant_id' => $merchantId];

        if ($validOnly) {
            $query .= " AND revoked_at IS NULL AND expires_at > :now";
            $params['now'] = (new \DateTime())->format(self::DATE_FORMAT);
        }
        $row = $this->doFetchOne($query, $params);

        return $row ? $this->factory->createFromArray($row) : null;
    }

    public function findNonRevokedByUuidAndMerchant(string $uuid, int $merchantId): ?MerchantUserInvitationEntity
    {
        $query = 'SELECT ' . implode(', ', self::SELECT_FIELDS) .
            ' FROM ' . self::TABLE_NAME .
            " WHERE uuid = :uuid AND merchant_id = :merchant_id AND revoked_at IS NULL";

        $params = ['uuid' => $uuid, 'merchant_id' => $merchantId];

        $row = $this->doFetchOne($query, $params);

        return $row ? $this->factory->createFromArray($row) : null;
    }

    public function searchInvitedUsers(int $merchantId, int $offset, int $limit, string $sortBy, string $sortDirection): SearchResultIterator
    {
        $table = self::TABLE_NAME;
        $merchantUsers = MerchantUserRepository::TABLE_NAME;
        $rolesTable = MerchantUserRoleRepository::TABLE_NAME;

        switch ($sortBy) {
            case 'invitation_status':
                $sorting = "1 {$sortDirection}, {$merchantUsers}.first_name ASC, {$merchantUsers}.last_name ASC";

                break;
            default:
                $sorting = "{$merchantUsers}.created_at {$sortDirection}";
        }

        $now = (new \DateTime())->format(self::DATE_FORMAT);

        $invitationStatus = "COALESCE(
               CASE
                   WHEN ({$table}.merchant_user_id > 0)
                       THEN '2,complete'
                   ELSE NULL END,
               CASE
                   WHEN ({$table}.expires_at <= '{$now}')
                       THEN '0,expired'
                   ELSE NULL END,
               CASE
                   WHEN ({$table}.expires_at > '{$now}')
                       THEN '1,pending'
                   ELSE NULL END,
               NULL
           ) AS invitation_status";

        $userRoleId = "
            IF({$merchantUsers}.role_id IS NULL,
                {$table}.merchant_user_role_id,
                {$merchantUsers}.role_id
            )";

        $queryColumns = implode(', ', [
            $invitationStatus,
            "{$merchantUsers}.first_name",
            "{$merchantUsers}.last_name",
            "{$merchantUsers}.user_id as merchant_user_uuid",
            "{$table}.merchant_id",
            "{$table}.merchant_user_id",
            $userRoleId . "AS merchant_user_role_id",
            "{$table}.uuid as invitation_uuid",
            "{$table}.email as invitation_email",
            "{$table}.created_at as invitation_created_at",
            "{$table}.expires_at as invitation_expires_at",
            "{$table}.revoked_at as invitation_revoked_at",
        ]);

        $query = "
            SELECT %s FROM {$table} 
            LEFT JOIN {$merchantUsers} ON {$table}.merchant_user_id = {$merchantUsers}.id
            LEFT JOIN {$rolesTable} ON {$rolesTable}.id = {$userRoleId}
            WHERE 
                ({$table}.merchant_id = :merchant_id) 
                AND ({$rolesTable}.name NOT IN (:billie_role_name, :none_role_name))
                AND (
                    ({$table}.merchant_user_id IS NOT NULL) 
                    OR 
                    ({$table}.revoked_at IS NULL)
                )
                AND ({$table}.email, {$table}.id) IN (
                  SELECT email, MAX(id) FROM {$table} WHERE merchant_id = :merchant_id2 GROUP BY email
                )
        ";

        $params['merchant_id'] = $params['merchant_id2'] = $merchantId;
        // never show the internal billie users or users without access/role (none role users should be deactivated)
        $params['billie_role_name'] = MerchantUserDefaultRoles::ROLE_BILLIE_ADMIN['name'];
        $params['none_role_name'] = MerchantUserDefaultRoles::ROLE_NONE['name'];
        $totalCount = $this->fetchCount(sprintf($query, "COUNT({$table}.id) as total"), $params);
        $query .= " ORDER BY {$sorting} LIMIT {$offset},{$limit}";
        $rows = $this->doFetchAll(sprintf($query, $queryColumns), $params);

        return new SearchResultIterator(
            $totalCount,
            $this->invitedUserFactory->createFromArrayCollection($rows)
        );
    }

    private function fetchCount(string $query, array $params, $countColumn = 'total'): int
    {
        $totalCount = $this->doFetchOne($query, $params);

        if (!isset($totalCount[$countColumn])) {
            throw new \InvalidArgumentException("Column {$countColumn} does not exist in the query result.");
        }

        return (int) $totalCount[$countColumn];
    }

    public function findValidByToken(string $token): ?MerchantUserInvitationEntity
    {
        $now = (new \DateTime())->format(self::DATE_FORMAT);
        $query = 'SELECT ' . implode(', ', self::SELECT_FIELDS) .
            ' FROM ' . self::TABLE_NAME .
            ' WHERE token = :token AND revoked_at IS NULL AND expires_at > :now';
        $params = ['token' => $token, 'now' => $now];
        $row = $this->doFetchOne($query, $params);

        return $row ? $this->factory->createFromArray($row) : null;
    }

    public function assignRoleToInvitation(int $id, int $roleId): void
    {
        $this->update($id, ['merchant_user_role_id' => $roleId]);
    }

    private function update(int $id, array $columnValuePairs): void
    {
        $sql = $this->generateUpdateQuery(self::TABLE_NAME, array_keys($columnValuePairs)). ' WHERE id = :id';
        $this->doExecute($sql, array_merge(['id' => $id], $columnValuePairs));
    }

    public function findOneByMerchantUserId(int $merchantUserId): ?MerchantUserInvitationEntity
    {
        $row = $this->doFetchOne(
            $this->generateSelectQuery(self::TABLE_NAME, self::SELECT_FIELDS)
            . ' WHERE merchant_user_id = :merchant_user_id',
            ['merchant_user_id' => $merchantUserId]
        );

        return $row ? $this->factory->createFromArray($row) : null;
    }
}
