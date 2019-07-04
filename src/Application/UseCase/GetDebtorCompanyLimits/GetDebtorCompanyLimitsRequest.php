<?php

namespace App\Application\UseCase\GetDebtorCompanyLimits;

use App\Application\UseCase\ValidatedRequestInterface;
use Symfony\Component\Validator\Constraints as Assert;

class GetDebtorCompanyLimitsRequest implements ValidatedRequestInterface
{
    /**
     * @Assert\Uuid()
     * @Assert\NotBlank()
     */
    private $uuid;

    public function __construct(string $uuid)
    {
        $this->uuid = $uuid;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }
}
