<?php

declare(strict_types=1);

namespace App\Application\UseCase\DeactivateUser;

use App\Application\UseCase\ValidatedRequestInterface;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @OA\Schema(
 *     schema="DeactivateUserRequest",
 *     title="User Deactivate Object",
 *     type="object",
 *     properties={
 *         @OA\Property(property="user_uuid", ref="#/components/schemas/UUID"),
 *     },
 *     required={"user_uuid"}
 * )
 */
class DeactivateUserRequest implements ValidatedRequestInterface
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
    private $currentUserUuid;

    public function getMerchantId(): ?int
    {
        return $this->merchantId;
    }

    public function setMerchantId($merchantId): self
    {
        $this->merchantId = $merchantId;

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

    public function getCurrentUserUuid(): ?string
    {
        return $this->currentUserUuid;
    }

    public function setCurrentUserUuid($currentUserUuid): self
    {
        $this->currentUserUuid = $currentUserUuid;

        return $this;
    }
}
