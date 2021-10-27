<?php

declare(strict_types=1);

namespace App\Tests\Unit\Http\Controller\PublicApi\Dashboard;

use App\Application\UseCase\GetMerchantPaymentDetails\GetMerchantPaymentDetailsRequest;
use App\Application\UseCase\GetMerchantPaymentDetails\GetMerchantPaymentDetailsResponse;
use App\Application\UseCase\GetMerchantPaymentDetails\GetMerchantPaymentDetailsUseCase;
use App\DomainModel\OrderInvoice\OrderInvoiceCollection;
use App\DomainModel\Payment\BankTransactionDetails;
use App\DomainModel\Payment\BankTransactionDetailsOrder;
use App\DomainModel\Payment\BankTransactionDetailsOrderCollection;
use App\DomainModel\PaymentMethod\PaymentMethod;
use App\Http\Controller\PublicApi\Dashboard\GetMerchantPaymentDetailsController;
use App\Http\HttpConstantsInterface;
use App\Tests\Unit\UnitTestCase;
use Ozean12\Borscht\Client\DomainModel\BankTransaction\BankTransaction;
use Ozean12\Borscht\Client\DomainModel\BankTransaction\BankTransactionTicketCollection;
use Ozean12\Borscht\Client\DomainModel\DirectDebit\DirectDebit;
use Ozean12\Money\Money;
use Ozean12\Sepa\Client\DomainModel\Mandate\SepaMandate;
use Ozean12\Support\ValueObject\BankAccount;
use Ozean12\Support\ValueObject\Iban;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;

class GetMerchantPaymentDetailsControllerTest extends UnitTestCase
{
    private const MERCHANT_ID = 123;

    private const IBAN = 'DE12500105179542622426';

    private const BIC = 'INGDDEFFXXX';

    private function createRequest(): Request
    {
        $request = Request::create('/');
        $request->attributes->set(
            HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID,
            self::MERCHANT_ID
        );

        return $request;
    }

    public function testShouldSucceedCallingUsecase(): void
    {
        $httpRequest = $this->createRequest();
        $useCase = $this->prophesize(GetMerchantPaymentDetailsUseCase::class);
        $bankTransaction = new BankTransaction(
            Uuid::uuid4(),
            false,
            new Money(0),
            new BankTransactionTicketCollection()
        );
        $transactionUuid = Uuid::uuid4();
        $orderUuid = Uuid::uuid4();
        $merchantDebtorUuid = Uuid::uuid4();
        $bankAccount = new BankAccount(
            new Iban(self::IBAN),
            self::BIC,
            'Test Bank',
            'John Smith'
        );
        $transactionDate = new \DateTimeImmutable('2021-01-01 10:00:00');

        $useCaseResponse = new GetMerchantPaymentDetailsResponse(
            new BankTransactionDetails(
                $transactionUuid,
                new Money(20),
                new Money(0),
                true,
                new BankTransactionDetailsOrderCollection(
                    [
                        new BankTransactionDetailsOrder(
                            $orderUuid,
                            new Money(100),
                            new Money(20),
                            new Money(80),
                            'EXT-ID',
                            'EXT-NUM'
                        ),
                    ]
                ),
                $merchantDebtorUuid,
                $bankAccount->getIban()->toString(),
                $bankAccount->getAccountHolder(),
                $transactionDate,
                'TRANSACTION-REF'
            ),
            new PaymentMethod(
                PaymentMethod::TYPE_BANK_TRANSFER,
                $bankAccount
            ),
            new OrderInvoiceCollection(),
            $bankTransaction
        );

        $useCase->execute(
            Argument::that(
                function (GetMerchantPaymentDetailsRequest $request) use ($transactionUuid) {
                    self::assertTrue($request->getTransactionUuid()->equals($transactionUuid));
                    self::assertEquals(self::MERCHANT_ID, $request->getMerchantId());

                    return true;
                }
            )
        )
            ->shouldBeCalledOnce()
            ->willReturn($useCaseResponse);

        $controller = new GetMerchantPaymentDetailsController($useCase->reveal());
        $expectedResponse = [
            'uuid' => $transactionUuid->toString(),
            'transaction_date' => '2021-01-01 10:00:00',
            'amount' => 20.00,
            'overpaid_amount' => 0.00,
            'is_allocated' => true,
            'merchant_debtor_uuid' => $merchantDebtorUuid->toString(),
            'transaction_counterparty_iban' => self::IBAN,
            'transaction_counterparty_name' => 'John Smith',
            'transaction_reference' => 'TRANSACTION-REF',
            'invoices' => [],
            'payment_method' => [
                    'type' => 'bank_transfer',
                    'data' => [
                            'iban' => 'DE12500105179542622426',
                            'bic' => 'INGDDEFFXXX',
                            'bank_name' => 'Test Bank',
                        ],
                ],
        ];
        $actualResponse = $controller->execute($transactionUuid, $httpRequest)->toArray();

        self::assertSame($expectedResponse, $actualResponse);
    }

    public function testShouldSucceedCallingUsecaseForDirectDebit(): void
    {
        $httpRequest = $this->createRequest();
        $useCase = $this->prophesize(GetMerchantPaymentDetailsUseCase::class);
        $transactionUuid = Uuid::uuid4();
        $orderUuid = Uuid::uuid4();
        $merchantDebtorUuid = Uuid::uuid4();
        $bankAccount = new BankAccount(
            new Iban(self::IBAN),
            self::BIC,
            'Test Bank',
            'John Smith'
        );
        $transactionDate = new \DateTimeImmutable('2021-01-01 10:00:00');

        $sepaMandateUuid = Uuid::uuid4();
        $sepaMandate = new SepaMandate(
            $sepaMandateUuid,
            'SEPA_REF',
            'CRED_ID',
            true,
            $bankAccount
        );
        $sepaMandateExecutionDate = new \DateTimeImmutable('2021-01-15 10:00:00');
        $sepaMandateState = DirectDebit::STATE_COMPLETED;
        $transactionDetails = new BankTransactionDetails(
            $transactionUuid,
            new Money(20),
            new Money(0),
            true,
            new BankTransactionDetailsOrderCollection(
                [
                    new BankTransactionDetailsOrder(
                        $orderUuid,
                        new Money(100),
                        new Money(20),
                        new Money(80),
                        'EXT-ID',
                        'EXT-NUM'
                    ),
                ]
            ),
            $merchantDebtorUuid,
            $bankAccount->getIban()->toString(),
            $bankAccount->getAccountHolder(),
            $transactionDate,
            'TRANSACTION-REF'
        );

        $useCaseResponse = new GetMerchantPaymentDetailsResponse(
            $transactionDetails,
            new PaymentMethod(
                PaymentMethod::TYPE_DIRECT_DEBIT,
                $bankAccount,
                $sepaMandate,
                $sepaMandateExecutionDate,
                $sepaMandateState
            ),
            new OrderInvoiceCollection(),
            new BankTransaction(
                $transactionUuid,
                $transactionDetails->isAllocated(),
                $transactionDetails->getOverPaidAmount(),
                new BankTransactionTicketCollection(),
                $transactionDetails->getMerchantDebtorUuid()
            )
        );

        $useCase->execute(
            Argument::that(
                function (GetMerchantPaymentDetailsRequest $request) use ($transactionUuid) {
                    self::assertTrue($request->getTransactionUuid()->equals($transactionUuid));
                    self::assertEquals(self::MERCHANT_ID, $request->getMerchantId());

                    return true;
                }
            )
        )
            ->shouldBeCalledOnce()
            ->willReturn($useCaseResponse);

        $controller = new GetMerchantPaymentDetailsController($useCase->reveal());
        $expectedResponse = [
            'uuid' => $transactionUuid->toString(),
            'transaction_date' => '2021-01-01 10:00:00',
            'amount' => 20.00,
            'overpaid_amount' => 0.00,
            'is_allocated' => true,
            'merchant_debtor_uuid' => $merchantDebtorUuid->toString(),
            'transaction_counterparty_iban' => self::IBAN,
            'transaction_counterparty_name' => 'John Smith',
            'transaction_reference' => 'TRANSACTION-REF',
            'invoices' => [],
            'payment_method' => [
                    'type' => 'direct_debit',
                    'data' => [
                            'iban' => 'DE12500105179542622426',
                            'bic' => 'INGDDEFFXXX',
                            'bank_name' => 'Test Bank',
                            'mandate_reference' => 'SEPA_REF',
                            'mandate_execution_date' => '2021-01-15 10:00:00',
                            'creditor_identification' => 'CRED_ID',
                        ],
                ],
        ];
        $actualResponse = $controller->execute($transactionUuid, $httpRequest)->toArray();

        self::assertSame($expectedResponse, $actualResponse);
    }
}
