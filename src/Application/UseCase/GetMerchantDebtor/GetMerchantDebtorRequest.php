<?php

namespace App\Application\UseCase\GetMerchantDebtor;

use App\Application\UseCase\ValidatedRequestInterface;
use Symfony\Component\Validator\Constraints as Assert;

class GetMerchantDebtorRequest implements ValidatedRequestInterface
{
    /**
     * @Assert\Type(type="integer")
     * @Assert\NotBlank()
     * @Assert\GreaterThan(0)
     * @var int
     */
    private $merchantId;

    /**
     * @Assert\Uuid()
     * @var string|null
     */
    private $merchantDebtorUuid;

    private $merchantDebtorExternalId;

    public function __construct(int $merchantId, ?string $merchantDebtorUuid, ?string $merchantDebtorExternalId)
    {
        $this->merchantId = $merchantId;
        $this->merchantDebtorUuid = $merchantDebtorUuid;
        $this->merchantDebtorExternalId = $merchantDebtorExternalId;

        if (is_null($merchantDebtorUuid) && is_null($merchantDebtorExternalId)) {
            throw new \InvalidArgumentException('Cannot have both parameters as null: merchantDebtorUuid and merchantDebtorExternalId.');
        }
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function getMerchantDebtorUuid(): ?string
    {
        return $this->merchantDebtorUuid;
    }

    public function getMerchantDebtorExternalId(): ?string
    {
        return $this->merchantDebtorExternalId;
    }
}
