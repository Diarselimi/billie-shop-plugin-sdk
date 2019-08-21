<?php

namespace App\DomainModel\Payment;

use App\DomainModel\Payment\RequestDTO\ConfirmRequestDTO;
use App\DomainModel\Payment\RequestDTO\CreateRequestDTO;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Payment\RequestDTO\ModifyRequestDTO;

class PaymentRequestFactory
{
    private function createFromOrderContainer(OrderContainer $orderContainer, AbstractPaymentRequestDTO $requestDTO)
    {
        $requestDTO
            ->setDuration($orderContainer->getOrderFinancialDetails()->getDuration())
            ->setAmountGross($orderContainer->getOrderFinancialDetails()->getAmountGross())
            ->setDebtorPaymentId($orderContainer->getMerchantDebtor()->getPaymentDebtorId())
            ;
        $this->createFromOrder($orderContainer->getOrder(), $requestDTO);
    }

    private function createFromOrder(OrderEntity $orderEntity, AbstractPaymentRequestDTO $requestDTO)
    {
        $requestDTO
            ->setPaymentId($orderEntity->getPaymentId())
            ->setInvoiceNumber($orderEntity->getInvoiceNumber())
            ->setExternalCode($orderEntity->getExternalCode())
            ->setShippedAt($orderEntity->getShippedAt())
            ;
    }

    public function createCreateRequestDTO(OrderContainer $orderContainer): CreateRequestDTO
    {
        $requestDTO = new CreateRequestDTO();
        $this->createFromOrderContainer($orderContainer, $requestDTO);

        return $requestDTO;
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
