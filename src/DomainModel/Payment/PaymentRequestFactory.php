<?php

namespace App\DomainModel\Payment;

use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Payment\RequestDTO\ConfirmRequestDTO;
use App\DomainModel\Payment\RequestDTO\ModifyRequestDTO;

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
            )
            ->setDebtorPaymentId($orderContainer->getMerchantDebtor()->getPaymentDebtorId());
        $this->createFromOrder($orderContainer->getOrder(), $requestDTO);
    }

    private function createFromOrder(OrderEntity $orderEntity, AbstractPaymentRequestDTO $requestDTO)
    {
        $requestDTO
            ->setPaymentId($orderEntity->getPaymentId())
            ->setInvoiceNumber($orderEntity->getInvoiceNumber())
            ->setExternalCode($orderEntity->getExternalCode())
            ->setShippedAt($orderEntity->getShippedAt());
    }

    public function createConfirmRequestDTO(OrderEntity $order, float $outstandingAmount): ConfirmRequestDTO
    {
        $requestDTO = new ConfirmRequestDTO($outstandingAmount);
        $this->createFromOrder($order, $requestDTO);

        return $requestDTO;
    }

    public function createModifyRequestDTO(OrderContainer $orderContainer): ModifyRequestDTO
    {
        $requestDTO = new ModifyRequestDTO();
        $this->createFromOrderContainer($orderContainer, $requestDTO);

        return $requestDTO;
    }
}
