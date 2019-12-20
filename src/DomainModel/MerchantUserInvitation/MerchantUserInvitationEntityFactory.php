<?php

namespace App\DomainModel\MerchantUserInvitation;

use App\Support\AbstractFactory;
use App\Support\RandomStringGenerator;
use Ramsey\Uuid\Uuid;

class MerchantUserInvitationEntityFactory extends AbstractFactory
{
    private const DEFAULT_INVITATION_EXPIRATION_TIME = '+1 day';

    private $tokenGenerator;

    public function __construct(RandomStringGenerator $tokenGenerator)
    {
        $this->tokenGenerator = $tokenGenerator;
    }

    public function createFromArray(array $data): MerchantUserInvitationEntity
    {
        return (new MerchantUserInvitationEntity())
            ->setId($data['id'])
            ->setEmail($data['email'])
            ->setMerchantId($data['merchant_id'])
            ->setMerchantUserRoleId($data['merchant_user_role_id'])
            ->setMerchantUserId($data['merchant_user_id'])
            ->setUuid($data['uuid'])
            ->setToken($data['token'])
            ->setRevokedAt($data['revoked_at'] ? new \DateTime($data['revoked_at']) : null)
            ->setExpiresAt(new \DateTime($data['expires_at']))
            ->setCreatedAt(new \DateTime($data['created_at']));
    }

    public function create(string $email, int $merchantId, int $roleId, int $merchantUserId = null): MerchantUserInvitationEntity
    {
        return (new MerchantUserInvitationEntity())
            ->setEmail($email)
            ->setMerchantId($merchantId)
            ->setMerchantUserRoleId($roleId)
            ->setMerchantUserId($merchantUserId)
            ->setUuid(Uuid::uuid4())
            ->setToken($this->tokenGenerator->generateHexToken())
            ->setExpiresAt((new \DateTime())->modify(self::DEFAULT_INVITATION_EXPIRATION_TIME))
            ->setCreatedAt(new \DateTime());
    }
}
