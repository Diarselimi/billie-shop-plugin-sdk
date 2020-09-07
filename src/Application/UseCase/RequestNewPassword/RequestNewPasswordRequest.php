<?php

declare(strict_types=1);

namespace App\Application\UseCase\RequestNewPassword;

use App\Application\UseCase\ValidatedRequestInterface;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @OA\Schema(
 *     schema="RequestNewPasswordRequest", required={"email"}, type="object", properties={
 *          @OA\Property(property="email", type="string", format="email", minLength=3, maxLength=180)
 *     }
 * )
 */
class RequestNewPasswordRequest implements ValidatedRequestInterface
{
    /**
     * @Assert\NotBlank
     * @Assert\Length(min="3", max="180")
     * @Assert\Email
     */
    private $email;

    public function __construct($email)
    {
        $this->email = $email;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
