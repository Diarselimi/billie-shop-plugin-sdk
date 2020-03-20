<?php

namespace App\Application\UseCase\GetMerchantNotifications;

use App\Application\UseCase\ValidatedRequestInterface;
use Symfony\Component\Validator\Constraints as Assert;

class GetMerchantNotificationsRequest implements ValidatedRequestInterface
{
    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="int")
     */
    private $merchantId;

    public function __construct(
        int $merchantId
    ) {
        $this->merchantId = $merchantId;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }
}
