<?php

declare(strict_types=1);

namespace App\DomainModel\MerchantUser;

use Billie\PdoBundle\DomainModel\AbstractTimestampableEntity;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="MerchantUserEntity", type="object", properties={
 *      @OA\Property(property="id", type="integer"),
 *      @OA\Property(property="uuid", ref="#/components/schemas/UUID"),
 *      @OA\Property(property="merchant_id", type="integer"),
 *      @OA\Property(property="signatory_power_uuid", ref="#/components/schemas/UUID"),
 *      @OA\Property(property="identity_verification_case_uuid", ref="#/components/schemas/UUID"),
 *      @OA\Property(property="role_id", type="integer"),
 *      @OA\Property(property="first_name", ref="#/components/schemas/TinyText"),
 *      @OA\Property(property="last_name", ref="#/components/schemas/TinyText"),
 *      @OA\Property(property="permissions", type="array"),
 * })
 */
class MerchantUserEntity extends AbstractTimestampableEntity
{
    private $uuid;

    private $merchantId;

    private $signatoryPowerUuid;

    private $identityVerificationCaseUuid;

    private $roleId;

    private $firstName;

    private $lastName;

    private $permissions;

    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * @param  string $uuid
     * @return $this
     */
    public function setUuid(string $uuid): MerchantUserEntity
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    /**
     * @param  int   $merchantId
     * @return $this
     */
    public function setMerchantId(int $merchantId): MerchantUserEntity
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    public function getSignatoryPowerUuid(): ?string
    {
        return $this->signatoryPowerUuid;
    }

    public function setSignatoryPowerUuid(?string $signatoryPowerUuid): MerchantUserEntity
    {
        $this->signatoryPowerUuid = $signatoryPowerUuid;

        return $this;
    }

    public function getIdentityVerificationCaseUuid(): ?string
    {
        return $this->identityVerificationCaseUuid;
    }

    public function setIdentityVerificationCaseUuid(?string $identityVerificationCaseUuid): MerchantUserEntity
    {
        $this->identityVerificationCaseUuid = $identityVerificationCaseUuid;

        return $this;
    }

    public function getRoleId(): int
    {
        return $this->roleId;
    }

    /**
     * @param  int   $roleId
     * @return $this
     */
    public function setRoleId(int $roleId): MerchantUserEntity
    {
        $this->roleId = $roleId;

        return $this;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param  string $firstName
     * @return $this
     */
    public function setFirstName(string $firstName): MerchantUserEntity
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @param  string $lastName
     * @return $this
     */
    public function setLastName(string $lastName): MerchantUserEntity
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * @param  array $permissions
     * @return $this
     */
    public function setPermissions(array $permissions): MerchantUserEntity
    {
        $this->permissions = $permissions;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'user_id' => $this->uuid,
            'merchant_id' => $this->merchantId,
            'signatory_power_uuid' => $this->signatoryPowerUuid,
            'identity_verification_case_uuid' => $this->identityVerificationCaseUuid,
            'role_id' => $this->roleId,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'permissions' => $this->permissions,
        ];
    }
}
