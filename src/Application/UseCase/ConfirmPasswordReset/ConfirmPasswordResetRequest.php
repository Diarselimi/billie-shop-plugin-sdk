<?php

declare(strict_types=1);

namespace App\Application\UseCase\ConfirmPasswordReset;

use App\Application\UseCase\ValidatedRequestInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ConfirmPasswordResetRequest implements ValidatedRequestInterface
{
    /**
     * @Assert\NotBlank()
     */
    private $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function getToken(): string
    {
        return $this->token;
    }
}
