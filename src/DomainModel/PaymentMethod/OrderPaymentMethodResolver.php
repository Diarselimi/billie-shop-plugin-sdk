<?php

declare(strict_types=1);

namespace App\DomainModel\PaymentMethod;

use App\DomainModel\BankAccount\BankAccountServiceException;
use App\DomainModel\BankAccount\BankAccountServiceInterface;
use App\DomainModel\BankAccount\BankNotFoundException;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Ozean12\Borscht\Client\DomainModel\BorschtClientInterface;
use Ozean12\Borscht\Client\DomainModel\Ticket\TicketNotFoundException;
use Ozean12\Sepa\Client\DomainModel\Mandate\SepaMandateNotFoundException;
use Ozean12\Sepa\Client\DomainModel\Mandate\SepaMandateNotValidException;
use Ozean12\Sepa\Client\DomainModel\SepaClientInterface;
use Ozean12\Support\HttpClient\Exception\HttpClientExceptionInterface;
use Ozean12\Support\ValueObject\BankAccount;
use Ozean12\Support\ValueObject\Exception\InvalidIbanException;
use Ozean12\Support\ValueObject\Iban;
use Ramsey\Uuid\Uuid;

class OrderPaymentMethodResolver implements LoggingInterface
{
    use LoggingTrait;

    private BankAccountServiceInterface $bankAccountService;

    private SepaClientInterface $sepaService;

    private BorschtClientInterface $borschtService;

    public function __construct(
        BankAccountServiceInterface $bankAccountService,
        SepaClientInterface $sepaService,
        BorschtClientInterface $borschtService
    ) {
        $this->bankAccountService = $bankAccountService;
        $this->sepaService = $sepaService;
        $this->borschtService = $borschtService;
    }

    public function getOrderPaymentMethods(OrderContainer $orderContainer): PaymentMethodCollection
    {
        if ($orderContainer->getOrder()->getMerchantDebtorId() === null) {
            // Debtor not identified
            return new PaymentMethodCollection([]);
        }

        try {
            $directDebitMethod = $this->getDirectDebitMethod($orderContainer);

            if ($directDebitMethod !== null) {
                return new PaymentMethodCollection([$directDebitMethod]);
            }

            $bankTransferMethod = $this->getBankTransferMethod($orderContainer);

            return new PaymentMethodCollection([$bankTransferMethod]);
        } catch (InvalidIbanException $exception) {
            $this->logWarning($exception->getMessage());

            return new PaymentMethodCollection([]);
        }
    }

    private function getBankTransferMethod(OrderContainer $orderContainer): PaymentMethod
    {
        $debtorPaymentDetails = $orderContainer->getDebtorPaymentDetails();

        $vibanAccount = new BankAccount(
            new Iban($debtorPaymentDetails->getBankAccountIban()),
            $debtorPaymentDetails->getBankAccountBic(),
            null,
            null
        );

        try {
            $bankName = $vibanAccount->getBankName() ?: $this->bankAccountService->getBankByBic(
                $vibanAccount->getBic()
            )->getName();
        } catch (BankAccountServiceException | BankNotFoundException $exception) {
            $this->logDebug(
                'Banco getBankByBic failed: ' . $exception->getMessage() . '. BIC was ' . $vibanAccount->getBic()
            );
            $bankName = null;
        }

        return new PaymentMethod(
            PaymentMethod::TYPE_BANK_TRANSFER,
            new BankAccount(
                $vibanAccount->getIban(),
                $vibanAccount->getBic(),
                $bankName,
                null
            )
        );
    }

    private function getDirectDebitMethod(OrderContainer $orderContainer): ?PaymentMethod
    {
        // Use the sepa mandate in the order as a fallback
        $sepaMandateUuid = $orderContainer->getOrder()->getDebtorSepaMandateUuid();
        $sepaMandateExecutionDate = null;

        // Preferably, for orders v1, get SEPA mandate info from borscht if there is a ticket and is direct debit
        $invoice = $orderContainer->getOrder()->isWorkflowV1() ? $orderContainer->getInvoices()->getFirst() : null;
        if ($invoice !== null) {
            try {
                $ticketPaymentDetails = $this->borschtService->getTicket(
                    Uuid::fromString($invoice->getPaymentUuid())
                );

                if ($ticketPaymentDetails->hasDirectDebitDetails()) {
                    $directDebit = $ticketPaymentDetails->getDirectDebitDetails();
                    $sepaMandateUuid = $directDebit->getSepaMandateUuid();
                    $sepaMandateExecutionDate = $directDebit->getExecutionDate();
                }
            } catch (HttpClientExceptionInterface | TicketNotFoundException $exception) {
                $this->logSuppressedException($exception);
            }
        }

        if ($sepaMandateUuid === null) {
            return null;
        }

        try {
            $sepaMandate = $this->sepaService->getMandate($sepaMandateUuid);
        } catch (SepaMandateNotValidException | SepaMandateNotFoundException $exception) {
            $this->logSuppressedException($exception);
            $sepaMandate = null;
        }

        if ($sepaMandate === null) {
            return null;
        }

        return new PaymentMethod(
            PaymentMethod::TYPE_DIRECT_DEBIT,
            $sepaMandate->getBankAccount(),
            $sepaMandate,
            $sepaMandateExecutionDate
        );
    }
}
