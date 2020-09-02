<?php

declare(strict_types=1);

namespace App\Application\UseCase\UpdateUserRole;

use App\Application\UseCase\ValidatedRequestInterface;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @OA\Schema(
 *     schema="UpdateUserRoleRequest",
 *     title="Role Update Object",
 *     type="object",
 *     properties={
 *         @OA\Property(property="user_uuid", ref="#/components/schemas/UUID"),
 *         @OA\Property(property="role_uuid", ref="#/components/schemas/UUID")
 *     },
 *     required={"user_uuid", "role_uuid"}
 * )
 */
class UpdateUserRoleRequest implements ValidatedRequestInterface
{
    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="integer")
     */
    private $merchantId;

    /**
     * @Assert\Uuid()
     * @Assert\NotBlank()
     */
    private $userUuid;

    /**
     * @Assert\Uuid()
     * @Assert\NotBlank()
     */
    private $roleUuid;

    public function getMerchantId(): ?int
    {
        return $this->merchantId;
    }

    public function setMerchantId($merchantId): self
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    public function getRoleUuid(): ?string
    {
        return $this->roleUuid;
    }

    public function setRoleUuid($roleUuid): self
    {
        $this->roleUuid = $roleUuid;

        return $this;
    }

    public function getUserUuid(): ?string
    {
        return $this->userUuid;
    }

    public function setUserUuid($userUuid): self
    {
        $this->userUuid = $userUuid;

        return $this;
    }
}
