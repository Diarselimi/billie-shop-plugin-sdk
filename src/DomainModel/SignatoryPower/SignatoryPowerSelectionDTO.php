<?php

namespace App\DomainModel\SignatoryPower;

use App\DomainModel\ArrayableInterface;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @OA\Schema(schema="SignatoryPowerDTO", title="Signatory Power Data transfer object", type="object", properties={
 *      @OA\Property(property="uuid", type="string"),
 *      @OA\Property(property="email", type="string", nullable=true),
 *      @OA\Property(property="is_identified_as_user", type="boolean"),
 * })
 */
class SignatoryPowerSelectionDTO implements ArrayableInterface
{
    /**
     * @Assert\NotBlank()
     * @Assert\Uuid()
     */
    private $uuid;

    /**
     * @Assert\Email()
     */
    private $email;

    /**
     * @Assert\Type(type="bool")
     */
    private $isIdentifiedAsUser;

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function setUuid($uuid): SignatoryPowerSelectionDTO
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail($email): SignatoryPowerSelectionDTO
    {
        $this->email = $email;

        return $this;
    }

    public function isIdentifiedAsUser(): bool
    {
        return $this->isIdentifiedAsUser;
    }

    public function setIsIdentifiedAsUser($isIdentifiedAsUser): SignatoryPowerSelectionDTO
    {
        $this->isIdentifiedAsUser = $isIdentifiedAsUser;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'email' => $this->email,
            'is_identified_as_user' => $this->isIdentifiedAsUser(),
        ];
    }
}
