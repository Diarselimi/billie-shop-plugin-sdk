<?php

namespace App\Application\UseCase\RegisterMerchantUser;

use App\Application\UseCase\ValidatedRequestInterface;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="RegisterMerchantUserRequest", title="Register Merchant User", type="object", properties={
 *     @OA\Property(property="first_name", type="string", nullable=false),
 *     @OA\Property(property="last_name", type="string", nullable=false),
 *     @OA\Property(property="email", format="email", type="string", nullable=false),
 *     @OA\Property(property="password", format="password", type="string", nullable=false),
 *     @OA\Property(property="role_uuid", type="integer", nullable=false),
 *     @OA\Property(
 *          property="permissions",
 *          type="array",
 *          nullable=true,
 *          default=null,
 *          @OA\Items(ref="#/components/schemas/MerchantUserPermissions")
 *      )
 * })
 */
class RegisterMerchantUserRequest implements ValidatedRequestInterface
{
    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="integer")
     */
    private $merchantId;

    /**
     * @Assert\NotBlank()
     */
    private $firstName;

    /**
     * @Assert\NotBlank()
     */
    private $lastName;

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
     * @Assert\Type(type="string")
     */
    private $roleUuid;

    /**
     * @Assert\Type(type="array")
     */
    private $permissions;

    public function __construct(
        $merchantId,
        $firstName,
        $lastName,
        $userEmail,
        $userPassword,
        $permissions,
        $roleUuid
    ) {
        $this->merchantId = $merchantId;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->userEmail = $userEmail;
        $this->userPassword = $userPassword;
        $this->permissions = $permissions;
        $this->roleUuid = $roleUuid;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getUserEmail(): string
    {
        return $this->userEmail;
    }

    public function getUserPassword(): string
    {
        return $this->userPassword;
    }

    public function getPermissions(): ?array
    {
        return $this->permissions;
    }

    public function getRoleUuid(): string
    {
        return $this->roleUuid;
    }
}
