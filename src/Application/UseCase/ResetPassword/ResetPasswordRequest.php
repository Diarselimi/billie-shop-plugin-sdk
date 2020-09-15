<?php

declare(strict_types=1);

namespace App\Application\UseCase\ResetPassword;

use App\Application\UseCase\ValidatedRequestInterface;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @OA\Schema(
 *     schema="ResetPasswordRequest", required={"password","token"}, type="object", properties={
 *          @OA\Property(property="password", type="string", format="password", minLength=8, maxLength=200),
 *          @OA\Property(property="token", type="string")
 *     }
 * )
 */
class ResetPasswordRequest implements ValidatedRequestInterface
{
    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="8", max="200")
     * @Assert\Regex(pattern="/\d/", message="The password should contain at least 1 number")
     * @Assert\Regex(pattern="/[a-zA-Z]/", message="The password should contain at least 1 letter")
     */
    private $plainPassword;

    /**
     * @Assert\NotBlank()
     */
    private $token;

    public function __construct($plainPassword, $token)
    {
        $this->plainPassword = $plainPassword;
        $this->token = $token;
    }

    public function getPlainPassword(): string
    {
        return $this->plainPassword;
    }

    public function getToken(): string
    {
        return $this->token;
    }
}
