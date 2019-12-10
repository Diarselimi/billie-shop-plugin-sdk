<?php

namespace App\Application\UseCase\RegisterInvitedMerchantUser;

use App\Application\UseCase\ValidatedRequestInterface;
use App\Application\Validator\Constraint as PaellaAssert;
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
 *          @OA\Property(property="tc_acepted", type="boolean"),
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

    /**
     * @PaellaAssert\InvitedUserTcAccepted()
     */
    private $tcAccepted;

    public function __construct(MerchantUserInvitationEntity $invitation, $firstName, $lastName, $password, $tcAccepted)
    {
        $this->invitation = $invitation;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->password = $password;
        $this->tcAccepted = $tcAccepted;
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

    public function isTcAccepted(): ?bool
    {
        return $this->tcAccepted;
    }
}
