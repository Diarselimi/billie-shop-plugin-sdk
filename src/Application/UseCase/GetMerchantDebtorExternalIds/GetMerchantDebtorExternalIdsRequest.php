<?php

declare(strict_types=1);

namespace App\Application\UseCase\GetMerchantDebtorExternalIds;

use App\Application\UseCase\ValidatedRequestInterface;
use Symfony\Component\Validator\Constraints as Assert;

class GetMerchantDebtorExternalIdsRequest implements ValidatedRequestInterface
{
    /**
     * @Assert\Type(type="integer")
     * @Assert\NotBlank()
     * @Assert\GreaterThan(0)
     */
    protected $merchantId;

    /**
     * @Assert\Uuid()
     * @Assert\NotBlank()
     */
    protected $merchantDebtorUuid;

    public function __construct($merchantId, $merchantDebtorUuid)
    {
        $this->merchantId = $merchantId;
        $this->merchantDebtorUuid = $merchantDebtorUuid;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function getMerchantDebtorUuid(): string
    {
        return $this->merchantDebtorUuid;
    }
}
