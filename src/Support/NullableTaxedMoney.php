<?php

declare(strict_types=1);

namespace App\Support;

use App\Application\Validator\Constraint as PaellaAssert;
use Ozean12\Money\Money;
use Ozean12\Money\Symfony\Validator as Assert;

/**
 * A copy-paste of TaxedMoney allowing 0 amount
 * @see Ozean12\Money\TaxedMoney\TaxedMoney
 *
 * @PaellaAssert\ValidNullableTaxSum()
 */
class NullableTaxedMoney
{
    /**
     * @Assert\Decimal\NotBlank()
     * @Assert\Decimal\IsNumeric()
     * @Assert\Decimal\GreaterThanOrEqual(value=0)
     * @Assert\Money\WholeCents()
     * @var Money
     */
    protected $gross;

    /**
     * @Assert\Decimal\NotBlank()
     * @Assert\Decimal\IsNumeric()
     * @Assert\Decimal\GreaterThanOrEqual(value=0)
     * @Assert\Money\WholeCents()
     * @var Money
     */
    protected $net;

    /**
     * @Assert\Decimal\NotBlank()
     * @Assert\Decimal\IsNumeric()
     * @Assert\Decimal\GreaterThanOrEqual(value=0)
     * @Assert\Money\WholeCents()
     * @var Money
     */
    protected $tax;

    final public function __construct(Money $gross, Money $net, Money $tax)
    {
        $this->gross = $gross;
        $this->net = $net;
        $this->tax = $tax;
    }

    public function getGross(): Money
    {
        return $this->gross;
    }

    public function getNet(): Money
    {
        return $this->net;
    }

    public function getTax(): Money
    {
        return $this->tax;
    }
}
