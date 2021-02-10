<?php

namespace spec\App\DomainModel\Invoice;

use App\DomainModel\Fee\Fee;
use App\DomainModel\Fee\FeeCalculator;
use App\DomainModel\Invoice\ExtendInvoiceService;
use App\DomainModel\Invoice\Invoice;
use App\DomainModel\MerchantSettings\MerchantSettingsEntity;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use Ozean12\Money\Money;
use Ozean12\Money\Percent;
use Ozean12\Money\TaxedMoney\TaxedMoney;
use Ozean12\Transfer\Message\Invoice\ExtendInvoice;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class ExtendInvoiceServiceSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(ExtendInvoiceService::class);
    }

    public function let(
        MessageBusInterface $messageBus,
        FeeCalculator $feeCalculator,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith(...func_get_args());
        $this->setLogger($logger);
    }

    public function it_should_dispatch_the_message(
        MessageBusInterface $messageBus,
        FeeCalculator $feeCalculator,
        Invoice $invoice,
        OrderContainer $orderContainer,
        Fee $fee
    ) {
        $grossAmount = new Money(100);
        $feeRates = [new Percent(1.5)];
        $uuid = 'uuid_string';
        $billingDate = new \DateTime('2020-06-30');
        $duration = 45;
        $externalCode = 'code123';

        $orderContainer->getMerchantSettings()->willReturn((new MerchantSettingsEntity())->setFeeRates($feeRates));
        $invoice->getAmount()->willReturn(new TaxedMoney($grossAmount, new Money(90), new Money(10)));
        $invoice->getUuid()->willReturn($uuid);
        $invoice->getBillingDate()->willReturn($billingDate);
        $invoice->getDuration()->willReturn(30);
        $invoice->getExternalCode()->willReturn($externalCode);

        $feeCalculator->calculate(
            $grossAmount,
            $duration,
            $feeRates
        )->willReturn($fee);

        $fee->getFeeRate()->willReturn(new Percent(1.5));
        $fee->getNetFeeAmount()->willReturn(new Money(5));
        $fee->getTaxFeeAmount()->willReturn(new Money(2));

        $messageBus->dispatch(Argument::that(
            function (ExtendInvoice $message) use ($uuid) {
                $invoice = $message->getInvoice();

                return $invoice->getUuid() === $uuid
                    && $invoice->getFeeRate() === 150
                    && $invoice->getNetFeeAmount() === 500
                    && $invoice->getVatOnFeeAmount() === 200
                    && $invoice->getDueDate() === '2020-08-14'
                    && $invoice->getDuration() === 45
                    && $invoice->getInvoiceReferences()->offsetExists('external_code')
                    && $invoice->getInvoiceReferences()['external_code'] === 'code123'
                ;
            }
        ))->shouldBeCalledOnce()->willReturn(new Envelope(new ExtendInvoice()));

        $this->extend($orderContainer, $invoice, $duration);
    }
}
