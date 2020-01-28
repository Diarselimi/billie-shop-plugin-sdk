<?php

namespace App\Http\Authentication;

use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class UserProvider
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @return mixed
     */
    private function getUserOfType(string $className): ?UserInterface
    {
        $user = $this->security->getUser();

        if ($user == null || get_class($user) !== $className) {
            return null;
        }

        return $user;
    }

    public function getUser(): ?AbstractUser
    {
        $user = $this->security->getUser();
        if (!($user instanceof AbstractUser)) {
            return null;
        }

        return $user;
    }

    public function getCheckoutUser(): ?CheckoutUser
    {
        return $this->getUserOfType(CheckoutUser::class);
    }

    public function getInvitedUser(): ?InvitedUser
    {
        return $this->getUserOfType(InvitedUser::class);
    }

    public function getMerchantApiUser(): ?MerchantApiUser
    {
        return $this->getUserOfType(MerchantApiUser::class);
    }

    public function getMerchantUser(): ?MerchantUser
    {
        return $this->getUserOfType(MerchantUser::class);
    }

    public function getSignatoryPowerTokenUser(): ?SignatoryPowerTokenUser
    {
        return $this->getUserOfType(SignatoryPowerTokenUser::class);
    }
}
