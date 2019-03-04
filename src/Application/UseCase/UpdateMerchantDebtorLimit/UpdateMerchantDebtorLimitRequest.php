<?php

namespace App\Application\UseCase\UpdateMerchantDebtorLimit;

use App\Application\UseCase\ValidatedRequestInterface;
use Symfony\Component\Validator\Constraints as Assert;
use App\Application\Validator\Constraint as PaellaAssert;

class UpdateMerchantDebtorLimitRequest implements ValidatedRequestInterface
{
    private $merchantDebtorExternalId;

    private $merchantId;

    /**
     * @Assert\NotBlank()
     * @Assert\GreaterThan(value=0)
     * @PaellaAssert\Number()
     */
    private $limit;

    public function __construct(string $merchantDebtorExternalId, int $merchantId, $limit)
    {
        $this->merchantDebtorExternalId = $merchantDebtorExternalId;
        $this->merchantId = $merchantId;
        $this->limit = $limit;
    }

    public function getMerchantDebtorExternalId(): string
    {
        return $this->merchantDebtorExternalId;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function getLimit(): float
    {
        return $this->limit;
    }
}
