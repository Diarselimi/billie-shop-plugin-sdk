<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;

class OrderRepository extends AbstractRepository implements OrderRepositoryInterface
{
    public function insert(OrderEntity $order): void
    {
        $id = $this->doInsert('
            INSERT INTO orders
            (amount, duration, external_code, state, external_comment, internal_comment, invoice_number, invoice_url, delivery_address_id, customer_id, company_id, debtor_person_id, debtor_external_data_id, payment_id, created_at, updated_at)
            VALUES
            (:amount, :duration, :external_code, :state, :external_comment, :internal_comment, :invoice_number, :invoice_url, :delivery_address_id, :customer_id, :company_id, :debtor_person_id, :debtor_external_data_id, :payment_id, :created_at, :updated_at)
            
        ', [
            'amount' => $order->getAmount(),
            'duration' => $order->getDuration(),
            'external_code' => $order->getExternalCode(),
            'state' => $order->getState(),
            'external_comment' => $order->getExternalCode(),
            'internal_comment' => $order->getInternalComment(),
            'invoice_number' => $order->getExternalComment(),
            'invoice_url' => $order->getInvoiceUrl(),
            'delivery_address_id' => $order->getDeliveryAddressId(),
            'customer_id' => $order->getCustomerId(),
            'company_id' => $order->getCompanyId(),
            'debtor_person_id' => $order->getDebtorPersonId(),
            'debtor_external_data_id' => $order->getDebtorExternalDataId(),
            'payment_id' => $order->getPaymentId(),
            'created_at' => $order->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $order->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);

        $order->setId($id);
    }

    public function getOneByExternalCode(string $externalCode, int $customerId):? OrderEntity
    {
        $order = $this->doFetch('
          SELECT id, amount, duration, external_code, state, external_comment, internal_comment, invoice_number, invoice_url, delivery_address_id, customer_id, company_id, debtor_person_id, debtor_external_data_id, payment_id, created_at, updated_at 
          FROM orders 
          WHERE external_code = :external_code AND customer_id = :customer_id
        ', [
            'external_code' => $externalCode,
            'customer_id' => $customerId,
        ]);

        if (!$order) {
            return null;
        }

        return (new OrderEntity())
            ->setId($order['id'])
            ->setAmount($order['amount'])
            ->setExternalCode($order['external_code'])
            ->setState($order['state'])
            ->setExternalComment($order['external_comment'])
            ->setInternalComment($order['internal_comment'])
            ->setInvoiceNumber($order['invoice_number'])
            ->setInvoiceUrl($order['invoice_url'])
            ->setDeliveryAddressId($order['delivery_address_id'])
            ->setCustomerId($order['customer_id'])
            ->setCompanyId($order['company_id'])
            ->setDebtorPersonId($order['debtor_person_id'])
            ->setDebtorExternalDataId($order['debtor_external_data_id'])
            ->setPaymentId($order['payment_id'])
            ->setCreatedAt(new \DateTime($order['created_at']))
            ->setUpdatedAt(new \DateTime($order['updated_at']))
        ;
    }
}
