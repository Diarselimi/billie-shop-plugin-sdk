<?php

namespace App\Http\Authentication;

use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationEntity;

class InvitedUser extends AbstractUser
{
    private const AUTH_ROLE = 'ROLE_AUTHENTICATED_AS_INVITED_USER';

    private $invitation;

    public function __construct(MerchantEntity $merchant, MerchantUserInvitationEntity $invitation)
    {
        parent::__construct($merchant);
        $this->invitation = $invitation;
    }

    public function getInvitation(): MerchantUserInvitationEntity
    {
        return $this->invitation;
    }

    public function getRoles(): array
    {
        return [self::AUTH_ROLE];
    }
}
