<?php

namespace App\DomainModel\Order;

use App\Application\UseCase\CreateOrder\CreateOrderRequest;
use App\Helper\Uuid\UuidGeneratorInterface;

class OrderEntityFactory
{
    private $uuidGenerator;

    public function __construct(UuidGeneratorInterface $uuidGenerator)
    {
        $this->uuidGenerator = $uuidGenerator;
    }

    public function createFromRequest(CreateOrderRequest $request): OrderEntity
    {
        return (new OrderEntity())
            ->setAmountForgiven(0)
            ->setExternalComment($request->getComment())
            ->setExternalCode($request->getExternalCode())
            ->setMerchantId($request->getMerchantId())
            ->setState(OrderStateManager::STATE_NEW)
            ->setUuid($this->uuidGenerator->uuid4())
            ->setCheckoutSessionId($request->getCheckoutSessionId())
        ;
    }

    public function createFromDatabaseRow(array $row): OrderEntity
    {
        return (new OrderEntity())
            ->setId($row['id'])
            ->setUuid($row['uuid'])
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
            ->setMarkedAsFraudAt($row['marked_as_fraud_at'] ? new \DateTime($row['marked_as_fraud_at']) : null)
            ->setCreatedAt(new \DateTime($row['created_at']))
            ->setUpdatedAt(new \DateTime($row['updated_at']))
            ->setCheckoutSessionId($row['checkout_session_id'])
            ->setCompanyBillingAddressUuid($row['company_billing_address_uuid'])
        ;
    }
}
