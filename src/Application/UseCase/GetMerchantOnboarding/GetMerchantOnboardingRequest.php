<?php

namespace App\Application\UseCase\GetMerchantOnboarding;

use App\Application\UseCase\ValidatedRequestInterface;
use Symfony\Component\Validator\Constraints as Assert;

class GetMerchantOnboardingRequest implements ValidatedRequestInterface
{
    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="integer")
     * @var int
     */
    private $merchantId;

    public function __construct($merchantId)
    {
        $this->merchantId = $merchantId;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }
}
