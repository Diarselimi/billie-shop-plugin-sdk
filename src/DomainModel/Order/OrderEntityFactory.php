<?php

namespace App\DomainModel\Order;

use App\Application\UseCase\CreateOrder\CreateOrderRequest;
use Ramsey\Uuid\Uuid;

class OrderEntityFactory
{
    public function createFromRequest(CreateOrderRequest $request): OrderEntity
    {
        return (new OrderEntity())
            ->setAmountNet($request->getAmount()->getNet())
            ->setAmountGross($request->getAmount()->getGross())
            ->setAmountTax($request->getAmount()->getTax())
            ->setAmountForgiven(0)
            ->setDuration($request->getDuration())
            ->setExternalComment($request->getComment())
            ->setExternalCode($request->getExternalCode())
            ->setMerchantId($request->getMerchantId())
            ->setState(OrderStateManager::STATE_NEW)
            ->setUuid(Uuid::uuid4()->toString())
        ;
    }

    public function createFromDatabaseRow(array $row): OrderEntity
    {
        return (new OrderEntity())
            ->setId($row['id'])
            ->setUuid($row['uuid'])
            ->setDuration($row['duration'])
            ->setAmountNet($row['amount_net'])
            ->setAmountGross($row['amount_gross'])
            ->setAmountTax($row['amount_tax'])
            ->setAmountForgiven(floatval($row['amount_forgiven']))
            ->setExternalCode($row['external_code'])
            ->setState($row['state'])
            ->setExternalComment($row['external_comment'])
            ->setInternalComment($row['internal_comment'])
            ->setInvoiceNumber($row['invoice_number'])
            ->setInvoiceUrl($row['invoice_url'])
            ->setProofOfDeliveryUrl($row['proof_of_delivery_url'])
            ->setDeliveryAddressId($row['delivery_address_id'])
            ->setMerchantDebtorId($row['merchant_debtor_id'])
            ->setMerchantId($row['merchant_id'])
            ->setDebtorPersonId($row['debtor_person_id'])
            ->setDebtorExternalDataId($row['debtor_external_data_id'])
            ->setPaymentId($row['payment_id'])
            ->setShippedAt($row['shipped_at'] ? new \DateTime($row['shipped_at']) : $row['shipped_at'])
            ->setMarkedAsFraudAt($row['marked_as_fraud_at'] ? new \DateTime($row['marked_as_fraud_at']) : $row['marked_as_fraud_at'])
            ->setCreatedAt(new \DateTime($row['created_at']))
            ->setUpdatedAt(new \DateTime($row['updated_at']))
        ;
    }
}
