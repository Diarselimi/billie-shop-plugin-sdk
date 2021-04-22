<?php

declare(strict_types=1);

namespace App\Tests\Unit\DomainModel\Invoice;

use App\DomainModel\Invoice\CreditNote\CreditNoteCollection;
use App\DomainModel\Invoice\CreditNote\CreditNoteFactory;
use App\DomainModel\Invoice\CreditNote\CreditNoteNotAllowedException;
use App\DomainModel\Invoice\CreditNote\InvoiceCreditNoteMessageFactory;
use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Invoice\InvoiceCancellationService;
use App\Tests\Unit\UnitTestCase;
use Ozean12\Money\Money;
use Ozean12\Money\TaxedMoney\TaxedMoneyFactory;
use Ozean12\Transfer\Message\CreditNote\CreateCreditNote;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\TraceableMessageBus;

class InvoiceCancellationServiceTest extends UnitTestCase
{
    /** @test */
    public function shouldSendCreditNoteWithFullAmounts()
    {
        $bus = new TraceableMessageBus(new MessageBus());
        $creditNoteFactory = new CreditNoteFactory();
        $creditNoteMessageFactory = new InvoiceCreditNoteMessageFactory();

        $invoiceCancellationService = new InvoiceCancellationService(
            $bus,
            $creditNoteFactory,
            $creditNoteMessageFactory
        );

        $invoice = (new Invoice())
            ->setAmount(TaxedMoneyFactory::create(540.00, 460, 100))
            ->setExternalCode('$externalCode')
            ->setOutstandingAmount(new Money(250.00))
            ->setDuration(35)
            ->setState(Invoice::STATE_NEW)
            ->setUuid(Uuid::uuid4()->toString())
            ->setCreatedAt(new \DateTime())
            ->setDueDate(new \DateTime('+30 DAYS'))
            ->setFeeAmount(TaxedMoneyFactory::create(100, 90, 10))
            ->setCreditNotes(new CreditNoteCollection([]))
            ->setMerchantPendingPaymentAmount(new Money(0))
            ->setInvoicePendingCancellationAmount(new Money(0));

        $invoiceCancellationService->cancelInvoiceFully($invoice);

        $message = $bus->getDispatchedMessages()['0']['message'];
        self::assertInstanceOf(CreateCreditNote::class, $message);
        self::assertNotEmpty($message);
        self::assertEquals(54000, $message->getGrossAmount());
        self::assertEquals(46000, $message->getNetAmount());
        self::assertEquals('cancelation', $message->getInternalComment());
    }

    /** @test */
    public function shouldThrowExceptionWhenStateIsNotSupported()
    {
        $bus = new TraceableMessageBus(new MessageBus());
        $creditNoteFactory = new CreditNoteFactory();
        $creditNoteMessageFactory = new InvoiceCreditNoteMessageFactory();

        $invoiceCancellationService = new InvoiceCancellationService(
            $bus,
            $creditNoteFactory,
            $creditNoteMessageFactory
        );

        $invoice = (new Invoice())
            ->setAmount(TaxedMoneyFactory::create(540.00, 460, 100))
            ->setExternalCode('$externalCode')
            ->setOutstandingAmount(new Money(250.00))
            ->setDuration(35)
            ->setState(Invoice::STATE_CANCELED)
            ->setUuid(Uuid::uuid4()->toString())
            ->setCreatedAt(new \DateTime())
            ->setDueDate(new \DateTime('+30 DAYS'))
            ->setFeeAmount(TaxedMoneyFactory::create(100, 90, 10))
            ->setCreditNotes(new CreditNoteCollection([]))
            ->setMerchantPendingPaymentAmount(new Money(0))
            ->setInvoicePendingCancellationAmount(new Money(0));

        $this->expectException(CreditNoteNotAllowedException::class);
        $invoiceCancellationService->cancelInvoiceFully($invoice);
    }
}
