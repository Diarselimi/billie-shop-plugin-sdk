<?php

declare(strict_types=1);

namespace App\Http\Factory;

use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderDeclinedReasonsMapper;
use App\Http\Response\DTO\AddressDTO;
use App\Http\Response\DTO\BankAccountDTO;
use App\Http\Response\DTO\Collection\InvoiceDTOCollection;
use App\Http\Response\DTO\DebtorDTO;
use App\Http\Response\DTO\DebtorExternalDataDTO;
use App\Http\Response\DTO\InvoiceDTO;
use App\Http\Response\DTO\OrderDTO as OrderResponseModel;

class OrderResponseFactory
{
    private OrderDeclinedReasonsMapper $declinedReasonsMapper;

    public function __construct(OrderDeclinedReasonsMapper $declinedReasonsMapper)
    {
        $this->declinedReasonsMapper = $declinedReasonsMapper;
    }

    public function create(OrderContainer $orderContainer): OrderResponseModel
    {
        return new OrderResponseModel(
            $orderContainer,
            $this->createInvoices($orderContainer),
            $this->createDebtor($orderContainer)
        );
    }

    private function createDebtor(OrderContainer $orderContainer): DebtorDTO
    {
        $address = null;
        $bankAccount = null;
        $order = $orderContainer->getOrder();
        $companyName = null;

        if ($order->getMerchantDebtorId()) {
            $companyName = $orderContainer->getDebtorCompany()->getName();
            $address = new AddressDTO($orderContainer->getDebtorCompany()->getAddress());

            if (
                !$orderContainer->getOrder()->isDeclined()   // @todo this should be fixed (just grab the data)
                && !$orderContainer->getOrder()->isWaiting()
            ) {
                $paymentDetails = $orderContainer->getDebtorPaymentDetails();
                $bankAccount = new BankAccountDTO(
                    $paymentDetails->getBankAccountIban(),
                    $paymentDetails->getBankAccountBic()
                );
            }
        }

        $companyAddress = null;

        return new DebtorDTO(
            $companyName,
            $address,
            new AddressDTO($orderContainer->getBillingAddress()),
            $bankAccount,
            new DebtorExternalDataDTO(
                $orderContainer->getDebtorExternalData()->getMerchantExternalId(),
                $orderContainer->getDebtorExternalData()->getName(),
                $orderContainer->getDebtorExternalData()->getIndustrySector(),
                new AddressDTO($orderContainer->getDebtorExternalDataAddress())
            )
        );
    }

    private function createInvoices(OrderContainer $orderContainer): InvoiceDTOCollection
    {
        $invoices = new InvoiceDTOCollection();
        foreach ($orderContainer->getInvoices()->toArray() as $invoice) {
            $invoices->add(new InvoiceDTO($invoice));
        }

        return $invoices;
    }
}
