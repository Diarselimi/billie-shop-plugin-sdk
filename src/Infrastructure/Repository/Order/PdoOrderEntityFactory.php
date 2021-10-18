<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository\Order;

use App\DomainModel\Order\OrderCollection;
use App\DomainModel\Order\OrderEntity;
use Ramsey\Uuid\Uuid;

class PdoOrderEntityFactory
{
    public function createFromRows(iterable $arrays): array
    {
        $orders = [];

        foreach ($arrays as $k => $item) {
            $orders[$k] = $this->create($item);
        }

        return $orders;
    }

    public function createCollection(iterable $arrays): OrderCollection
    {
        return new OrderCollection($this->createFromRows($arrays));
    }

    public function create(array $row): OrderEntity
    {
        $durationExtension = $row['duration_extension'] === null ? null : (int) $row['duration_extension'];

        return (new OrderEntity())
            ->setId((int) $row['id'])
            ->setUuid($row['uuid'])
            ->setAmountForgiven(floatval($row['amount_forgiven']))
            ->setExternalCode($row['external_code'])
            ->setState($row['state'])
            ->setExternalComment($row['external_comment'])
            ->setInternalComment($row['internal_comment'])
            ->setInvoiceNumber($row['invoice_number'])
            ->setInvoiceUrl($row['invoice_url'])
            ->setProofOfDeliveryUrl($row['proof_of_delivery_url'])
            ->setDeliveryAddressId((int) $row['delivery_address_id'])
            ->setMerchantDebtorId((int) $row['merchant_debtor_id'])
            ->setMerchantId((int) $row['merchant_id'])
            ->setDebtorPersonId((int) $row['debtor_person_id'])
            ->setDebtorExternalDataId((int) $row['debtor_external_data_id'])
            ->setPaymentId($row['payment_id'])
            ->setShippedAt($row['shipped_at'] ? new \DateTime($row['shipped_at']) : $row['shipped_at'])
            ->setCreatedAt(new \DateTime($row['created_at']))
            ->setUpdatedAt(new \DateTime($row['updated_at']))
            ->setCheckoutSessionId((int) $row['checkout_session_id'])
            ->setCreationSource($row['creation_source'])
            ->setCompanyBillingAddressUuid($row['company_billing_address_uuid'])
            ->setWorkflowName($row['workflow_name'])
            ->setDurationExtension($durationExtension)
            ->setDebtorSepaMandateUuid($row['debtor_sepa_mandate_uuid'] ? Uuid::fromString($row['debtor_sepa_mandate_uuid']) : null);
    }
}
