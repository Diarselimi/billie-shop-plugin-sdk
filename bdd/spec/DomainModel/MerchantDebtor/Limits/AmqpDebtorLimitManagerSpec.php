<?php

namespace spec\App\DomainModel\MerchantDebtor\Limits;

use App\DomainModel\MerchantDebtor\Limits\AmqpDebtorLimitManager;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;

class AmqpDebtorLimitManagerSpec extends ObjectBehavior
{
    public function let(LoggerInterface $logger)
    {
        $this->setLogger($logger);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(AmqpDebtorLimitManager::class);
    }
}
