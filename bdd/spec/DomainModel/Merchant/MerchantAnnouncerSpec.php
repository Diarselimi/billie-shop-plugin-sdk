<?php

namespace spec\App\DomainModel\Merchant;

use App\DomainModel\Merchant\MerchantAnnouncer;
use Ozean12\Transfer\Message\Customer\CustomerCreated;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class MerchantAnnouncerSpec extends ObjectBehavior
{
    const COMPANY_UUID = 'company_uuid';

    const PAYMENT_UUID = 'payment_uuid';

    const COMPANY_NAME = 'billie';

    public function let(
        MessageBusInterface $bus,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith($bus, 'paella-test');
        $this->setLogger($logger);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(MerchantAnnouncer::class);
    }

    public function it_should_send_message_successfully(
        MessageBusInterface $bus
    ) {
        $message = (new CustomerCreated())
            ->setCompanyUuid(self::COMPANY_UUID)
            ->setUuid(self::PAYMENT_UUID)
            ->setName(self::COMPANY_NAME)
            ->setType('merchant')
            ->setInvestorUuid('paella-test');

        $bus->dispatch($message)->shouldBeCalledOnce()->willReturn(new Envelope($message));

        $this->customerCreated(
            self::COMPANY_UUID,
            self::COMPANY_NAME,
            self::PAYMENT_UUID
        );
    }
}
