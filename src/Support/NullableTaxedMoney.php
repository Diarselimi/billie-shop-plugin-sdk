<?php

declare(strict_types=1);

namespace App\Support;

use Ozean12\Money\Money;
use Ozean12\Money\Symfony\Validator as Assert;
use Ozean12\Money\TaxedMoney\TaxedMoney;

/**
 * A copy-paste of TaxedMoney allowing 0 amount
 * @see TaxedMoney
 *
 * @Assert\TaxedMoney\ValidTaxSum()
 */
class NullableTaxedMoney extends TaxedMoney
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
}
