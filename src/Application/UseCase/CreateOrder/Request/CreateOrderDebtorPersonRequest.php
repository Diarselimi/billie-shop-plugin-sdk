<?php

namespace App\Application\UseCase\CreateOrder\Request;

use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="CreateOrderDebtorPersonRequest",
 *     title="Debtor Person",
 *     required={"gender", "email"},
 *     properties={
 *          @OA\Property(property="salutation", maxLength=1, enum={"m", "f"}, type="string"),
 *          @OA\Property(property="first_name", ref="#/components/schemas/TinyText", example="James"),
 *          @OA\Property(property="last_name", ref="#/components/schemas/TinyText", example="Smith"),
 *          @OA\Property(property="phone_number", ref="#/components/schemas/PhoneNumber"),
 *          @OA\Property(property="email", format="email", type="string", example="james.smith@example.com")
 *     }
 * )
 */
class CreateOrderDebtorPersonRequest
{
    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     * @Assert\Choice({"m", "f"})
     */
    private $gender;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(max=255)
     */
    private $firstName;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(max=255)
     */
    private $lastName;

    /**
     * @Assert\Regex(pattern="/^(\+|\d|\()[ \-\/0-9()]{5,20}$/", match=true)
     */
    private $phoneNumber;

    /**
     * @Assert\NotBlank()
     * @Assert\Email(mode="strict")
     */
    private $email;

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): CreateOrderDebtorPersonRequest
    {
        $this->gender = $gender ? strtolower($gender) : null;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): CreateOrderDebtorPersonRequest
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): CreateOrderDebtorPersonRequest
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): CreateOrderDebtorPersonRequest
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): CreateOrderDebtorPersonRequest
    {
        $this->email = $email ? strtolower($email) : null;

        return $this;
    }
}
