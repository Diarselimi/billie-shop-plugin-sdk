<?php

namespace App\Application\UseCase\UpdateMerchantDebtorLimit;

use App\Application\UseCase\ValidatedRequestInterface;
use App\Application\Validator\Constraint as PaellaAssert;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateMerchantDebtorLimitRequest implements ValidatedRequestInterface
{
    /**
     * @Assert\NotBlank()
     * @Assert\Uuid()
     */
    private $merchantDebtorUuid;

    /**
     * @Assert\NotBlank()
     * @Assert\GreaterThan(value=0)
     * @PaellaAssert\Number()
     */
    private $limit;

    public function __construct(string $uuid, $limit)
    {
        $this->merchantDebtorUuid = $uuid;
        $this->limit = $limit;
    }

    public function getMerchantDebtorUuid(): string
    {
        return $this->merchantDebtorUuid;
    }

    public function getLimit(): float
    {
        return $this->limit;
    }
}
