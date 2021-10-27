<?php

declare(strict_types=1);

namespace App\DomainModel\PaymentMethod;

use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Ozean12\Borscht\Client\DomainModel\BankTransaction\BankTransaction;
use Ozean12\Borscht\Client\DomainModel\BankTransaction\BankTransactionTicket;
use Ozean12\Borscht\Client\DomainModel\BorschtClientInterface;
use Ozean12\Borscht\Client\DomainModel\Debtor\Debtor;
use Ozean12\Sepa\Client\DomainModel\SepaClientInterface;
use Ramsey\Uuid\UuidInterface;

class BankTransactionPaymentMethodResolver implements LoggingInterface
{
    use LoggingTrait;

    private BankNameResolver $bankNameResolver;

    private SepaClientInterface $sepaService;

    private BorschtClientInterface $borschtService;

    public function __construct(
        BankNameResolver $bankNameResolver,
        SepaClientInterface $sepaService,
        BorschtClientInterface $borschtService
    ) {
        $this->bankNameResolver = $bankNameResolver;
        $this->sepaService = $sepaService;
        $this->borschtService = $borschtService;
    }

    public function getPaymentMethod(
        BankTransaction $transaction,
        ?UuidInterface $debtorPaymentUuid
    ): ?PaymentMethod {
        if ($debtorPaymentUuid === null) {
            return null;
        }

        if (!$transaction->isAllocated()) {
            return null;
        }

        $debtor = $this->borschtService->getDebtor($debtorPaymentUuid);
        $paymentMethods = [];
        $sepaMandateUuids = [];

        // Bank transaction payment method is provided by the BTS service, but it doesn't return the sepa mandate uuid
        // so, we would have to loop the tickets anyway to find it. Because of that, calling BTS would be not worthy.

        foreach ($transaction->getTickets() as $ticket) {
            /** @var BankTransactionTicket $ticket */
            $paymentMethod = $this->resolveTicketPaymentMethod($ticket, $debtor);

            $paymentMethods[$paymentMethod->getType()] = $paymentMethod;

            if ($paymentMethod->getSepaMandate() !== null) {
                $sepaMandateUuids[] = $paymentMethod->getSepaMandate()->getUuid();
            }
        }

        if (count($paymentMethods) === 0) {
            return null;
        }

        if (count($paymentMethods) > 1) {
            $this->logError(
                sprintf('The associated tickets of transaction "%s" have different payment methods', $transaction->getUuid()->toString())
            );
        }

        if (count($sepaMandateUuids) > 1) {
            $this->logError(
                sprintf('The associated tickets of transaction "%s" have different SEPA mandates', $transaction->getUuid()->toString())
            );
        }

        return $paymentMethods[PaymentMethod::TYPE_BANK_TRANSFER] ??
            $paymentMethods[PaymentMethod::TYPE_DIRECT_DEBIT];
    }

    private function resolveTicketPaymentMethod(BankTransactionTicket $ticket, Debtor $debtor): PaymentMethod
    {
        if (!$ticket->hasDirectDebitData()) {
            return new PaymentMethod(
                PaymentMethod::TYPE_BANK_TRANSFER,
                $this->bankNameResolver->resolve($debtor->getBankAccount())
            );
        }

        // It is safe to assume that if it has DD data, the transaction is DD, because when DD fails that data
        // is cleared from the ticket in Borscht.

        $sepaMandate = $this->sepaService->getMandate($ticket->getDirectDebitData()->getSepaMandateUuid());

        return new PaymentMethod(
            PaymentMethod::TYPE_DIRECT_DEBIT,
            $this->bankNameResolver->resolve($sepaMandate->getBankAccount()),
            $sepaMandate,
            $ticket->getDirectDebitData()->getExecutionDate(),
            $ticket->getDirectDebitData()->getState()
        );
    }
}
