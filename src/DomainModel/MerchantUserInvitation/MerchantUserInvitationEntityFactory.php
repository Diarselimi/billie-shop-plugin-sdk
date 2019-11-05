<?php

namespace App\DomainModel\MerchantUserInvitation;

use App\Support\AbstractFactory;
use App\Support\TokenGenerator;
use Ramsey\Uuid\Uuid;

class MerchantUserInvitationEntityFactory extends AbstractFactory
{
    private $tokenGenerator;

    public function __construct(TokenGenerator $tokenGenerator)
    {
        $this->tokenGenerator = $tokenGenerator;
    }

    private const DEFAULT_INVITATION_EXPIRATION_TIME = '+1 day';

    public function createFromArray(array $data): MerchantUserInvitationEntity
    {
        throw new \LogicException(__METHOD__ . " not implemented");
    }

    public function create(string $email, int $merchantId, int $roleId, int $merchantUserId = null): MerchantUserInvitationEntity
    {
        return (new MerchantUserInvitationEntity())
            ->setEmail($email)
            ->setMerchantId($merchantId)
            ->setMerchantUserRoleId($roleId)
            ->setMerchantUserId($merchantUserId)
            ->setUuid(Uuid::uuid4())
            ->setToken($this->tokenGenerator->generate(32, 20))
            ->setExpiresAt((new \DateTime())->modify(self::DEFAULT_INVITATION_EXPIRATION_TIME))
            ->setCreatedAt(new \DateTime());
    }
}
