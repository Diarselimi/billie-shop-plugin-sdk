<?php

namespace spec\App\DomainModel\Merchant;

use App\DomainModel\Merchant\MerchantAnnouncer;
use Ozean12\Transfer\Message\Customer\CustomerCreated;
use Ozean12\Transfer\Message\Customer\CustomerFeeRatesUpdated;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class MerchantAnnouncerSpec extends ObjectBehavior
{
    const COMPANY_UUID = 'company_uuid';

    const PAYMENT_UUID = 'payment_uuid';

    const COMPANY_NAME = 'billie';

    const FEE_RATES = ["14" => 299, "30" => 349];

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

    public function it_should_send_customer_created_message_successfully(
        MessageBusInterface $bus
    ) {
        $message = (new CustomerCreated())
            ->setCompanyUuid(self::COMPANY_UUID)
            ->setUuid(self::PAYMENT_UUID)
            ->setName(self::COMPANY_NAME)
            ->setType('merchant')
            ->setInvestorUuid('paella-test');

        $bus->dispatch($message)->shouldBeCalledOnce()->willReturn(new Envelope($message));

        $this->announceCustomerCreated(
            self::COMPANY_UUID,
            self::COMPANY_NAME,
            self::PAYMENT_UUID
        );
    }

    public function it_should_send_customer_fee_rates_updated_message_successfully(
        MessageBusInterface $bus
    ) {
        $message = (new CustomerFeeRatesUpdated())
            ->setFeeRates(self::FEE_RATES)
            ->setCompanyUuid(self::PAYMENT_UUID);

        $bus->dispatch($message)->shouldBeCalledOnce()->willReturn(new Envelope($message));

        $this->announceCustomerFeeRatesUpdated(
            self::PAYMENT_UUID,
            self::FEE_RATES
        );
    }
}
