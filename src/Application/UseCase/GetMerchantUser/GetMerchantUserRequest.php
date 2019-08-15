<?php

namespace App\Application\UseCase\GetMerchantUser;

class GetMerchantUserRequest
{
    private $userId;

    public function __construct(string $userId)
    {
        $this->userId = $userId;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }
}
