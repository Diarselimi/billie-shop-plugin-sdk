<?php

namespace App\Application\UseCase\RegisterInvitedMerchantUser;

use App\Application\UseCase\ValidatedRequestInterface;
use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationEntity;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @OA\Schema(
 *      schema="RegisterInvitedMerchantUserRequest",
 *      x={"groups": {"private"}},
 *      type="object",
 *      properties={
 *          @OA\Property(property="first_name", ref="#/components/schemas/TinyText"),
 *          @OA\Property(property="last_name", ref="#/components/schemas/TinyText"),
 *          @OA\Property(property="password", ref="#/components/schemas/TinyText", minLength=6),
 *      },
 *      required={"first_name", "last_name", "password"}
 * )
 */
class RegisterInvitedMerchantUserRequest implements ValidatedRequestInterface
{
    private $invitation;

    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     */
    private $firstName;

    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     */
    private $lastName;

    /**
     * @Assert\NotBlank()
     * @Assert\Regex(pattern="/^(?=.*\d).{8,}$/", message="The password should be at least 8 characters long and contain at least one letter and one digit")
     * @Assert\Type(type="string")
     */
    private $password;

    public function __construct(MerchantUserInvitationEntity $invitation, $firstName, $lastName, $password)
    {
        $this->invitation = $invitation;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->password = $password;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getInvitation(): MerchantUserInvitationEntity
    {
        return $this->invitation;
    }
}
