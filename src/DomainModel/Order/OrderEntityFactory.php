<?php

namespace App\DomainModel\Order;

use App\Application\UseCase\CreateOrder\CreateOrderRequest;

class OrderEntityFactory
{
    public function createFromRequest(CreateOrderRequest $request): OrderEntity
    {
        return (new OrderEntity())
            ->setAmountNet($request->getAmountNet())
            ->setAmountGross($request->getAmountGross())
            ->setAmountTax($request->getAmountTax())
            ->setDuration($request->getDuration())
            ->setExternalComment($request->getComment())
            ->setExternalCode($request->getExternalCode())
            ->setMerchantId($request->getMerchantId())
            ->setState(OrderStateManager::STATE_NEW)
        ;
    }

    public function createFromDatabaseRow(array $row): OrderEntity
    {
        return (new OrderEntity())
            ->setId($row['id'])
            ->setDuration($row['duration'])
            ->setAmountNet($row['amount_net'])
            ->setAmountGross($row['amount_gross'])
            ->setAmountTax($row['amount_tax'])
            ->setExternalCode($row['external_code'])
            ->setState($row['state'])
            ->setExternalComment($row['external_comment'])
            ->setInternalComment($row['internal_comment'])
            ->setInvoiceNumber($row['invoice_number'])
            ->setInvoiceUrl($row['invoice_url'])
            ->setDeliveryAddressId($row['delivery_address_id'])
            ->setMerchantDebtorId($row['merchant_debtor_id'])
            ->setMerchantId($row['merchant_id'])
            ->setDebtorPersonId($row['debtor_person_id'])
            ->setDebtorExternalDataId($row['debtor_external_data_id'])
            ->setPaymentId($row['payment_id'])
            ->setShippedAt($row['shipped_at'] ? new \DateTime($row['shipped_at']) : $row['shipped_at'])
            ->setCreatedAt(new \DateTime($row['created_at']))
            ->setUpdatedAt(new \DateTime($row['updated_at']))
        ;
    }
}
