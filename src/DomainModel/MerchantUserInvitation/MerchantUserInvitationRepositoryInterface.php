<?php

namespace App\DomainModel\MerchantUserInvitation;

use App\Infrastructure\Repository\SearchResultIterator;

interface MerchantUserInvitationRepositoryInterface
{
    /**
     * @param  int                                           $merchantId
     * @param  int                                           $offset
     * @param  int                                           $limit
     * @param  string                                        $sortBy
     * @param  string                                        $sortDirection
     * @return MerchantInvitedUserDTO[]|SearchResultIterator
     */
    public function searchInvitedUsers(
        int $merchantId,
        int $offset,
        int $limit,
        string $sortBy,
        string $sortDirection
    ): SearchResultIterator;

    public function create(MerchantUserInvitationEntity $invitation): void;

    public function registerToMerchantUser(MerchantUserInvitationEntity $invitation): void;

    public function findByEmailAndMerchant(string $email, int $merchantId, bool $validOnly): ?MerchantUserInvitationEntity;

    public function findNonRevokedByUuidAndMerchant(string $uuid, int $merchantId): ?MerchantUserInvitationEntity;

    public function revokeValidByEmailAndMerchant(string $email, int $merchantId): void;

    public function findValidByToken(string $token): ?MerchantUserInvitationEntity;

    public function assignRoleToInvitation(int $id, int $roleId): void;

    public function findOneByMerchantUserId(int $merchantUserId): ?MerchantUserInvitationEntity;
}
