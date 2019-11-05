<?php

namespace App\DomainModel\MerchantUserInvitation;

use Billie\PdoBundle\DomainModel\AbstractEntity;
use Billie\PdoBundle\DomainModel\CreatedAtAwareEntity;
use Billie\PdoBundle\DomainModel\CreatedAtEntityTrait;

class MerchantInvitedUserDTO extends AbstractEntity implements CreatedAtAwareEntity
{
    use CreatedAtEntityTrait;

    public const INVITATION_STATUSES = [
        self::INVITATION_STATUS_PENDING,
        self::INVITATION_STATUS_EXPIRED,
        self::INVITATION_STATUS_COMPLETE,
    ];

    public const INVITATION_STATUS_PENDING = 'pending';

    public const INVITATION_STATUS_EXPIRED = 'expired';

    public const INVITATION_STATUS_COMPLETE = 'complete';

    private $userId;

    private $merchantId;

    private $roleId;

    private $firstName;

    private $lastName;

    private $email;

    private $invitationUuid;

    private $invitationStatus;

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(?string $userId): MerchantInvitedUserDTO
    {
        $this->userId = $userId;

        return $this;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function setMerchantId(int $merchantId): MerchantInvitedUserDTO
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    public function getRoleId(): int
    {
        return $this->roleId;
    }

    public function setRoleId(int $roleId): MerchantInvitedUserDTO
    {
        $this->roleId = $roleId;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): MerchantInvitedUserDTO
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): MerchantInvitedUserDTO
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): MerchantInvitedUserDTO
    {
        $this->email = $email;

        return $this;
    }

    public function getInvitationUuid(): string
    {
        return $this->invitationUuid;
    }

    public function setInvitationUuid(string $invitationUuid): MerchantInvitedUserDTO
    {
        $this->invitationUuid = $invitationUuid;

        return $this;
    }

    public function getInvitationStatus(): string
    {
        return $this->invitationStatus;
    }

    public function setInvitationStatus(string $invitationStatus): MerchantInvitedUserDTO
    {
        $this->invitationStatus = $invitationStatus;

        return $this;
    }
}
