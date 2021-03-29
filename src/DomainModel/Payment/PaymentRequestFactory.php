<?php

namespace App\DomainModel\Payment;

use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Payment\RequestDTO\ConfirmRequestDTO;
use App\DomainModel\Payment\RequestDTO\ModifyRequestDTO;
use Ozean12\Money\Money;

class PaymentRequestFactory
{
    private function createFromOrderContainer(OrderContainer $orderContainer, AbstractPaymentRequestDTO $requestDTO)
    {
        $requestDTO
            ->setDuration($orderContainer->getOrderFinancialDetails()->getDuration())
            ->setDebtorPaymentId($orderContainer->getMerchantDebtor()->getPaymentDebtorId())
            ->setAmountGross(
                $orderContainer
                    ->getOrderFinancialDetails()->getAmountGross()
                    ->subtract($orderContainer->getInvoices()->getInvoicesCreditNotesGrossSum())
                    ->toFloat()
            );
        $this->createFromOrder($orderContainer->getOrder(), $requestDTO);
    }

    private function createFromOrder(OrderEntity $orderEntity, AbstractPaymentRequestDTO $requestDTO)
    {
        $requestDTO
            ->setPaymentUuid($orderEntity->getPaymentId())
            ->setInvoiceNumber($orderEntity->getInvoiceNumber())
            ->setExternalCode($orderEntity->getExternalCode())
            ->setShippedAt($orderEntity->getShippedAt());
    }

    public function createConfirmRequestDTO(OrderEntity $order, float $paidAmount): ConfirmRequestDTO
    {
        $requestDTO = new ConfirmRequestDTO($paidAmount);
        $this->createFromOrder($order, $requestDTO);

        return $requestDTO;
    }

    public function createConfirmRequestDTOFromInvoice(
        Invoice $invoice,
        Money $paidAmount,
        string $orderExternalCode
    ): ConfirmRequestDTO {
        $requestDTO = new ConfirmRequestDTO($paidAmount->getMoneyValue());
        $requestDTO
            ->setPaymentUuid($invoice->getPaymentUuid())
            ->setInvoiceNumber($invoice->getExternalCode())
            ->setExternalCode($orderExternalCode)
            ->setShippedAt($invoice->getCreatedAt());

        return $requestDTO;
    }

    public function createModifyRequestDTO(OrderContainer $orderContainer): ModifyRequestDTO
    {
        $requestDTO = new ModifyRequestDTO();
        $this->createFromOrderContainer($orderContainer, $requestDTO);

        return $requestDTO;
    }
}
