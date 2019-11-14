<?php

namespace App\Application\UseCase\GetInvitedMerchantUser;

use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationEntity;

class GetInvitedMerchantUserRequest
{
    private $invitation;

    public function __construct(MerchantUserInvitationEntity $invitation)
    {
        $this->invitation = $invitation;
    }

    public function getInvitation(): MerchantUserInvitationEntity
    {
        return $this->invitation;
    }
}
