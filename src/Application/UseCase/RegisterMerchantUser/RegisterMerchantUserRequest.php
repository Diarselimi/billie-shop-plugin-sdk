<?php

namespace App\Application\UseCase\RegisterMerchantUser;

use App\Application\UseCase\ValidatedRequestInterface;
use App\DomainModel\MerchantUser\MerchantUserEntity;
use Symfony\Component\Validator\Constraints as Assert;

class RegisterMerchantUserRequest implements ValidatedRequestInterface
{
    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="integer")
     */
    private $merchantId;

    /**
     * @Assert\NotBlank()
     * @Assert\Email(mode="strict")
     */
    private $userEmail;

    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     */
    private $userPassword;

    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="array")
     */
    private $roles;

    public function __construct(
        int $merchantId,
        string $userEmail,
        string $userPassword,
        array $roles = [MerchantUserEntity::ROLE_USER]
    ) {
        $this->merchantId = $merchantId;
        $this->userEmail = $userEmail;
        $this->userPassword = $userPassword;
        $this->roles = $roles;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function getUserEmail(): string
    {
        return $this->userEmail;
    }

    public function getUserPassword(): string
    {
        return $this->userPassword;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }
}
