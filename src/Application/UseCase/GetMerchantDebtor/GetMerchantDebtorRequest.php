<?php

namespace App\Application\UseCase\GetMerchantDebtor;

use App\Application\UseCase\ValidatedRequestInterface;
use Symfony\Component\Validator\Constraints as Assert;

class GetMerchantDebtorRequest implements ValidatedRequestInterface
{
    /**
     * @Assert\Type(type="integer")
     * @Assert\GreaterThan(0)
     * @var int|null
     */
    protected $merchantId;

    /**
     * @Assert\Uuid()
     * @Assert\NotBlank()
     * @var string
     */
    protected $merchantDebtorUuid;

    public function __construct(?int $merchantId, string $merchantDebtorUuid)
    {
        $this->merchantId = $merchantId;
        $this->merchantDebtorUuid = $merchantDebtorUuid;
    }

    public function getMerchantId(): ?int
    {
        return $this->merchantId;
    }

    public function getMerchantDebtorUuid(): string
    {
        return $this->merchantDebtorUuid;
    }
}
