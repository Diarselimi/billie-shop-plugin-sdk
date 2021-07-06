<?php

declare(strict_types=1);

namespace App\Application\UseCase\GetInvoicePayments\Response;

use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Payment\BankTransaction;
use App\DomainModel\Payment\BankTransactionFactory;
use App\Support\PaginatedCollection;
use Ozean12\Money\Money;

class GetInvoicePaymentsResponseFactory
{
    private BankTransactionFactory $bankTransactionFactory;

    public function __construct(BankTransactionFactory $bankTransactionFactory)
    {
        $this->bankTransactionFactory = $bankTransactionFactory;
    }

    public function create(Invoice $invoice, PaginatedCollection $collection): GetInvoicePaymentsResponse
    {
        $transactions = $this->bankTransactionFactory->createFromArrayMultiple($collection);

        $response = new GetInvoicePaymentsResponse();
        $summary = new InvoicePaymentSummary();
        $totalPaid = new Money(0.0);

        if ($collection->getTotal() > 0) {
            $summary->setDeductibleAmount(new Money($collection->getItems()[0]['deductible_amount']));
        }

        foreach ($transactions as $transaction) {
            $response->addItem($transaction);

            switch ($transaction->getType()) {
                case BankTransaction::TYPE_MERCHANT_PAYMENT:
                    {
                        if ($transaction->getState() === BankTransaction::STATE_NEW) {
                            $summary->setPendingMerchantPaymentAmount(
                                $summary->getPendingMerchantPaymentAmount()->add($transaction->getAmount())
                            );
                        } elseif ($transaction->getState() === BankTransaction::STATE_COMPLETE) {
                            $summary->setMerchantPaymentAmount(
                                $summary->getMerchantPaymentAmount()->add($transaction->getAmount())
                            );
                            $totalPaid = $totalPaid->add($transaction->getAmount());
                        }
                    }

                    break;
                case BankTransaction::TYPE_INVOICE_PAYBACK:
                    {
                        if ($transaction->getState() === BankTransaction::STATE_COMPLETE) {
                            $summary->setDebtorPaymentAmount(
                                $summary->getDebtorPaymentAmount()->add($transaction->getAmount())
                            );
                            $totalPaid = $totalPaid->add($transaction->getAmount());
                        }
                    }

                    break;
                case BankTransaction::TYPE_INVOICE_CANCELLATION:
                    {
                        if ($transaction->getState() !== BankTransaction::STATE_COMPLETE) {
                            break;
                        }
                        $summary->setCancellationAmount(
                            $summary->getCancellationAmount()->add($transaction->getAmount())
                        );
                    }

                    break;
            }
        }

        $summary->setPendingCancellationAmount($invoice->getInvoicePendingCancellationAmount());
        $summary->setTotalPaymentAmount($totalPaid);

        $response->setSummary($summary);

        return $response;
    }
}
