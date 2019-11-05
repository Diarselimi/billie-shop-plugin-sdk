<?php

namespace App\DomainModel\MerchantUserInvitation;

use Billie\PdoBundle\DomainModel\AbstractEntity;
use Billie\PdoBundle\DomainModel\CreatedAtAwareEntity;
use Billie\PdoBundle\DomainModel\CreatedAtEntityTrait;

class MerchantUserInvitationEntity extends AbstractEntity implements CreatedAtAwareEntity
{
    use CreatedAtEntityTrait;

    private $uuid;

    private $token;

    private $merchantId;

    private $merchantUserId;

    private $merchantUserRoleId;

    private $email;

    private $expiresAt;

    private $revokedAt;

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): MerchantUserInvitationEntity
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): MerchantUserInvitationEntity
    {
        $this->token = $token;

        return $this;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function setMerchantId(int $merchantId): MerchantUserInvitationEntity
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    public function getMerchantUserId(): ?int
    {
        return $this->merchantUserId;
    }

    public function setMerchantUserId(?int $merchantUserId): MerchantUserInvitationEntity
    {
        $this->merchantUserId = $merchantUserId;

        return $this;
    }

    public function getMerchantUserRoleId(): int
    {
        return $this->merchantUserRoleId;
    }

    public function setMerchantUserRoleId(int $merchantUserRoleId): MerchantUserInvitationEntity
    {
        $this->merchantUserRoleId = $merchantUserRoleId;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): MerchantUserInvitationEntity
    {
        $this->email = $email;

        return $this;
    }

    public function getExpiresAt(): \DateTime
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(\DateTime $expiresAt): MerchantUserInvitationEntity
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function getRevokedAt(): ?\DateTime
    {
        return $this->revokedAt;
    }

    public function setRevokedAt(?\DateTime $revokedAt): MerchantUserInvitationEntity
    {
        $this->revokedAt = $revokedAt;

        return $this;
    }
}
