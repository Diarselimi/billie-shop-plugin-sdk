<?php

declare(strict_types=1);

namespace App\Application\UseCase\CheckoutProvideIban;

use Ozean12\Sepa\Client\DomainModel\Mandate\SepaMandate;

class CheckoutProvideIbanResponse
{
    private SepaMandate $mandate;

    private string $creditorName;

    public function __construct(SepaMandate $mandate, string $creditorName)
    {
        $this->mandate = $mandate;
        $this->creditorName = $creditorName;
    }

    public function getMandate(): SepaMandate
    {
        return $this->mandate;
    }

    public function getCreditorName(): string
    {
        return $this->creditorName;
    }
}
