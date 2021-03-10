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
        $transactions = $this->bankTransactionFactory->createFromArrayCollection($collection);

        $response = new GetInvoicePaymentsResponse();
        $summary = new InvoicePaymentSummary();
        $totalPaid = new Money(0.0);

        foreach ($transactions as $transaction) {
            $response->addItem($transaction);

            switch ($transaction->getType()) {
                case BankTransaction::TYPE_MERCHANT_PAYMENT:
                    {
                        if ($transaction->getState() === BankTransaction::STATE_NEW) {
                            $summary->setMerchantUnmappedAmount(
                                $summary->getMerchantUnmappedAmount()->add($transaction->getAmount())
                            );
                        } elseif ($transaction->getState() === BankTransaction::STATE_COMPLETE) {
                            $summary->setMerchantPaidAmount(
                                $summary->getMerchantPaidAmount()->add($transaction->getAmount())
                            );
                            $totalPaid = $totalPaid->add($transaction->getAmount());
                        }
                    }

                    break;
                case BankTransaction::TYPE_INVOICE_PAYBACK:
                    {
                        if ($transaction->getState() === BankTransaction::STATE_NEW) {
                            $summary->setDebtorUnmappedAmount(
                                $summary->getDebtorUnmappedAmount()->add($transaction->getAmount())
                            );
                        } elseif ($transaction->getState() === BankTransaction::STATE_COMPLETE) {
                            $summary->setDebtorPaidAmount(
                                $summary->getDebtorPaidAmount()->add($transaction->getAmount())
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
                        $summary->setCancelledAmount(
                            $summary->getCancelledAmount()->add($transaction->getAmount())
                        );
                    }

                    break;
            }
        }

        $summary->setTotalPaidAmount($totalPaid);
        $summary->setOpenAmount($invoice->getAmount()->getGross()->subtract($totalPaid));

        $response->setSummary($summary);

        return $response;
    }
}
