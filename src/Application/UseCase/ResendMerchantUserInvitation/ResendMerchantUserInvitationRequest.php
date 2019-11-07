<?php

namespace App\Application\UseCase\ResendMerchantUserInvitation;

use App\Application\UseCase\ValidatedRequestInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ResendMerchantUserInvitationRequest implements ValidatedRequestInterface
{
    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="integer")
     * @var int
     */
    private $merchantId;

    /**
     * @Assert\Uuid()
     * @Assert\NotBlank()
     */
    private $uuid;

    public function __construct(int $merchantId, $uuid)
    {
        $this->merchantId = $merchantId;
        $this->uuid = $uuid;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }
}
