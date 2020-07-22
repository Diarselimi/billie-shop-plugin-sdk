<?php

namespace App\Application\UseCase\VerifyNewDebtorExternalCode;

use App\Application\UseCase\ValidatedRequestInterface;
use Symfony\Component\Validator\Constraints as Assert;

class VerifyNewDebtorExternalCodeRequest implements ValidatedRequestInterface
{
    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="int")
     */
    private $merchantId;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=255)
     */
    private $externalCode;

    public function __construct($merchantId, $externalCode)
    {
        $this->merchantId = $merchantId;
        $this->externalCode = $externalCode;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function getExternalCode(): string
    {
        return $this->externalCode;
    }
}
