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
        $invoicePendingCancellationAmount = new Money(10);
        $paginatedCollection = new PaginatedCollection();
        $invoice = (new Invoice())->setAmount(
            new TaxedMoney(new Money($invoiceGrossAmount), new Money($invoiceGrossAmount), new Money(0))
        )->setInvoicePendingCancellationAmount($invoicePendingCancellationAmount);
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
        self::assertMoneyEquals($invoicePendingCancellationAmount, $summary->getPendingCancellationAmount());
        self::assertMoneyEquals($expectedSummary->getTotalPaymentAmount(), $summary->getTotalPaymentAmount());
        self::assertMoneyEquals($expectedSummary->getCancellationAmount(), $summary->getCancellationAmount());
        self::assertMoneyEquals($expectedSummary->getDebtorPaymentAmount(), $summary->getDebtorPaymentAmount());
        self::assertMoneyEquals($expectedSummary->getMerchantPaymentAmount(), $summary->getMerchantPaymentAmount());
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
                ],
                (new InvoicePaymentSummary())
                    ->setTotalPaymentAmount(new Money(55))
                    ->setCancellationAmount(new Money(5))
                    ->setDebtorPaymentAmount(new Money(20))
                    ->setMerchantPaymentAmount(new Money(35))
                    ->setPendingMerchantPaymentAmount(new Money(2)),
            ],
        ];
    }
}
