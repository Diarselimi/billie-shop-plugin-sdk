<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\UseCase\GetInvoicePayments\Response;

use App\Application\UseCase\GetInvoicePayments\Response\GetInvoicePaymentsResponseFactory;
use App\Application\UseCase\GetInvoicePayments\Response\InvoicePaymentSummary;
use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Payment\BankTransaction;
use App\DomainModel\Payment\BankTransactionFactory;
use App\Support\PaginatedCollection;
use App\Tests\Unit\UnitTestCase;
use Ozean12\Money\Money;
use Ozean12\Money\TaxedMoney\TaxedMoney;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @see GetInvoicePaymentsResponseFactory
 */
class GetInvoicePaymentsResponseFactoryTest extends UnitTestCase
{
    /**
     * @var ObjectProphecy|BankTransactionFactory
     */
    private ObjectProphecy $bankTransactionFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bankTransactionFactory = $this->prophesize(BankTransactionFactory::class);
    }

    /**
     * @test
     *
     * @dataProvider createShouldMatchDataProvider
     * @param float                 $invoiceGrossAmount
     * @param array                 $transactionsData
     * @param InvoicePaymentSummary $expectedSummary
     */
    public function createShouldMatch(
        float $invoiceGrossAmount,
        array $transactionsData,
        InvoicePaymentSummary $expectedSummary
    ): void {
        $paginatedCollection = new PaginatedCollection();
        $invoice = (new Invoice())->setAmount(
            new TaxedMoney(new Money($invoiceGrossAmount), new Money($invoiceGrossAmount), new Money(0))
        );
        $transactions = array_map(
            function (array $data) {
                return (new BankTransaction())
                    ->setAmount(new Money($data['amount']))
                    ->setState($data['state'])
                    ->setType($data['type']);
            },
            $transactionsData
        );

        $this->bankTransactionFactory->createFromArrayMultiple($paginatedCollection)
            ->shouldBeCalledOnce()->willReturn($transactions);

        $responseFactory = new GetInvoicePaymentsResponseFactory($this->bankTransactionFactory->reveal());

        $response = $responseFactory->create($invoice, $paginatedCollection);
        $summary = $response->getSummary();

        self::assertCount(count($transactionsData), $response->getItems());
        self::assertMoneyEquals($expectedSummary->getTotalPaidAmount(), $summary->getTotalPaidAmount());
        self::assertMoneyEquals($expectedSummary->getCancelledAmount(), $summary->getCancelledAmount());
        self::assertMoneyEquals($expectedSummary->getDebtorPaidAmount(), $summary->getDebtorPaidAmount());
        self::assertMoneyEquals($expectedSummary->getMerchantPaidAmount(), $summary->getMerchantPaidAmount());
        self::assertMoneyEquals($expectedSummary->getOpenAmount(), $summary->getOpenAmount());
    }

    public function createShouldMatchDataProvider(): array
    {
        return [
            [
                100.00,
                [
                    [
                        'amount' => 20.00,
                        'state' => BankTransaction::STATE_COMPLETE,
                        'type' => BankTransaction::TYPE_INVOICE_PAYBACK,
                    ],
                    [
                        'amount' => 1.00,
                        'state' => BankTransaction::STATE_NEW,
                        'type' => BankTransaction::TYPE_INVOICE_PAYBACK,
                    ],
                    [
                        'amount' => 29.85,
                        'state' => BankTransaction::STATE_COMPLETE,
                        'type' => BankTransaction::TYPE_MERCHANT_PAYMENT,
                    ],
                    [
                        'amount' => 5.15,
                        'state' => BankTransaction::STATE_COMPLETE,
                        'type' => BankTransaction::TYPE_MERCHANT_PAYMENT,
                    ],
                    [
                        'amount' => 2.00,
                        'state' => BankTransaction::STATE_NEW,
                        'type' => BankTransaction::TYPE_MERCHANT_PAYMENT,
                    ],
                    [
                        'amount' => 5.00,
                        'state' => BankTransaction::STATE_COMPLETE,
                        'type' => BankTransaction::TYPE_INVOICE_CANCELLATION,
                    ],
                    [
                        'amount' => 3.00,
                        'state' => BankTransaction::STATE_NEW,
                        'type' => BankTransaction::TYPE_INVOICE_CANCELLATION,
                    ],
                ],
                (new InvoicePaymentSummary())
                    ->setOpenAmount(new Money(45))
                    ->setTotalPaidAmount(new Money(55))
                    ->setCancelledAmount(new Money(5))
                    ->setDebtorPaidAmount(new Money(20))
                    ->setMerchantPaidAmount(new Money(35))
                    ->setMerchantUnmappedAmount(new Money(2))
                    ->setDebtorUnmappedAmount(new Money(1)),
            ],
        ];
    }
}
