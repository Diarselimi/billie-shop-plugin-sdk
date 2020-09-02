<?php

declare(strict_types=1);

namespace App\Application\UseCase\UpdateInvitationRole;

use App\Application\UseCase\ValidatedRequestInterface;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @OA\Schema(
 *     schema="UpdateInvitationRoleRequest",
 *     title="Role Update Object",
 *     type="object",
 *     properties={
 *         @OA\Property(property="email", type="string", format="email"),
 *         @OA\Property(property="role_uuid", ref="#/components/schemas/UUID")
 *     },
 *     required={"email", "role_uuid"}
 * )
 */
class UpdateInvitationRoleRequest implements ValidatedRequestInterface
{
    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="integer")
     */
    private $merchantId;

    /**
     * @Assert\Email()
     * @Assert\NotBlank()
     */
    private $email;

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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail($email): self
    {
        $this->email = $email;

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
}
