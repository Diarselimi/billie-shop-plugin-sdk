<?php

namespace App\Application\UseCase\CreateMerchantUserInvitation;

use App\Application\UseCase\ValidatedRequestInterface;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @OA\Schema(
 *      schema="CreateMerchantUserInvitationRequest",
 *      x={"groups": {"private"}},
 *      type="object",
 *      required={"email", "role_uuid"},
 *      properties={
 *          @OA\Property(property="email", type="string", format="email"),
 *          @OA\Property(property="role_uuid", ref="#/components/schemas/UUID"),
 *      }
 * )
 */
class CreateMerchantUserInvitationRequest implements ValidatedRequestInterface
{
    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="integer")
     * @var int
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

    public function __construct(int $merchantId, $email, $roleUuid)
    {
        $this->merchantId = $merchantId;
        $this->email = $email;
        $this->roleUuid = $roleUuid;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getRoleUuid(): string
    {
        return $this->roleUuid;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }
}
