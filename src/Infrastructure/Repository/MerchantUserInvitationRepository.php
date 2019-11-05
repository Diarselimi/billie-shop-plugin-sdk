<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\MerchantUser\MerchantUserDefaultRoles;
use App\DomainModel\MerchantUserInvitation\MerchantInvitedUserDTOFactory;
use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationEntity;
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

    public function __construct(MerchantInvitedUserDTOFactory $invitedUserFactory)
    {
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

    public function createIfNotExistsForUser(MerchantUserInvitationEntity $invitation): void
    {
        if (!$invitation->getMerchantUserId()) {
            throw new \LogicException("User ID is not set");
        }

        $total = $this->fetchCount(
            "SELECT COUNT(id) AS total FROM " . self::TABLE_NAME . " WHERE merchant_user_id = :user_id",
            ['user_id' => $invitation->getMerchantUserId()]
        );

        if ($total > 0) {
            return;
        }

        $this->create($invitation);
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

        $queryColumns = implode(', ', [
            $invitationStatus,
            "{$merchantUsers}.first_name",
            "{$merchantUsers}.last_name",
            "{$merchantUsers}.user_id as merchant_user_uuid",
            "{$table}.merchant_id",
            "{$table}.merchant_user_id",
            "{$table}.merchant_user_role_id",
            "{$table}.uuid as invitation_uuid",
            "{$table}.email as invitation_email",
            "{$table}.created_at as invitation_created_at",
            "{$table}.expires_at as invitation_expires_at",
            "{$table}.revoked_at as invitation_revoked_at",
        ]);

        $query = "
            SELECT %s FROM {$table} 
            LEFT JOIN {$merchantUsers} ON {$table}.merchant_user_id = {$merchantUsers}.id
            LEFT JOIN {$rolesTable} ON {$table}.merchant_user_role_id = {$rolesTable}.id
            WHERE 
                ({$table}.merchant_id = :merchant_id) 
                AND ({$rolesTable}.name != :billie_role_name)
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
        // never show the internal billie users
        $params['billie_role_name'] = MerchantUserDefaultRoles::ROLE_BILLIE_ADMIN['name'];
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
}
