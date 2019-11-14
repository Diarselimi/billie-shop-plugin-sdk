<?php

namespace App\Http\Authentication;

class MerchantApiUser extends AbstractUser
{
    private const AUTH_ROLE = 'ROLE_AUTHENTICATED_AS_MERCHANT';

    public function getRoles(): array
    {
        return [self::AUTH_ROLE];
    }
}
