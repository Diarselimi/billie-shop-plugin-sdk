<?php

namespace App\Application\UseCase\MerchantUserLogin;

use App\Application\UseCase\ValidatedRequestInterface;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="MerchantUserLoginRequest", title="Merchant User Login", type="object", properties={
 *     @OA\Property(property="email", format="email", type="string", nullable=false),
 *     @OA\Property(property="password", format="password", type="string", nullable=false)
 * }, required={"email", "password"})
 */
class MerchantUserLoginRequest implements ValidatedRequestInterface
{
    /**
     * @Assert\NotBlank()
     * @Assert\Email(mode="strict")
     */
    private $email;

    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     */
    private $password;

    public function __construct($email, $password)
    {
        $this->email = $email;
        $this->password = $password;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}
