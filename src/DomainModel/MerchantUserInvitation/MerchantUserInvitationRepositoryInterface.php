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

    public function createIfNotExistsForUser(MerchantUserInvitationEntity $invitation): void;
}
