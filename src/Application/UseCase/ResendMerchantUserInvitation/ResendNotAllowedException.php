<?php

namespace App\Application\UseCase\ResendMerchantUserInvitation;

class ResendNotAllowedException extends \RuntimeException
{
    protected $message = 'Resend not allowed on active invitations';
}
