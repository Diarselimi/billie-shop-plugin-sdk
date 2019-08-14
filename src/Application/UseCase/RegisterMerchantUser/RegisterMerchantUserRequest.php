<?php

namespace App\Application\UseCase\RegisterMerchantUser;

use App\Application\UseCase\ValidatedRequestInterface;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="RegisterMerchantUserRequest", title="Register Merchant User", type="object", properties={
 *     @OA\Property(property="email", format="email", type="string", nullable=false),
 *     @OA\Property(property="password", format="password", type="string", nullable=false),
 *     @OA\Property(
 *          property="roles",
 *          type="array",
 *          nullable=true,
 *          @OA\Items(ref="#/components/schemas/MerchantUserRoles")
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
        array $roles
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
