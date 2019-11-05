<?php

namespace App\DomainModel\MerchantUserInvitation;

class MerchantUserInvitationNotFoundException extends \RuntimeException
{
    protected $message = "Merchant User Invitation not found";
}
