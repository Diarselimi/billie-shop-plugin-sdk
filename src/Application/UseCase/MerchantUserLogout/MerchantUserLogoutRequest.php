<?php

namespace App\Application\UseCase\MerchantUserLogout;

use App\Application\UseCase\ValidatedRequestInterface;
use Symfony\Component\Validator\Constraints as Assert;

class MerchantUserLogoutRequest implements ValidatedRequestInterface
{
    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     */
    private $userAccessToken;

    public function __construct(string $userAccessToken)
    {
        $this->userAccessToken = $userAccessToken;
    }

    public function getUserAccessToken()
    {
        return $this->userAccessToken;
    }
}
