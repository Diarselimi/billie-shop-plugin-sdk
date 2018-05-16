<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\Exception\RepositoryException;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderLifecycleEvent;
use App\DomainModel\Order\OrderRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class OrderRepository extends AbstractRepository implements OrderRepositoryInterface
{
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function insert(OrderEntity $order): void
    {
        $id = $this->doInsert('
            INSERT INTO orders
            (amount_net, amount_gross, amount_tax, duration, external_code, state, external_comment, internal_comment, invoice_number, invoice_url, delivery_address_id, customer_id, company_id, debtor_person_id, debtor_external_data_id, payment_id, created_at, updated_at)
            VALUES
            (:amount_net, :amount_gross, :amount_tax, :duration, :external_code, :state, :external_comment, :internal_comment, :invoice_number, :invoice_url, :delivery_address_id, :customer_id, :company_id, :debtor_person_id, :debtor_external_data_id, :payment_id, :created_at, :updated_at)
            
        ', [
            'amount_net' => $order->getAmountNet(),
            'amount_gross' => $order->getAmountGross(),
            'amount_tax' => $order->getAmountTax(),
            'duration' => $order->getDuration(),
            'external_code' => $order->getExternalCode(),
            'state' => $order->getState(),
            'external_comment' => $order->getExternalComment(),
            'internal_comment' => $order->getInternalComment(),
            'invoice_number' => $order->getInvoiceNumber(),
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

    public function getOneByExternalCode(string $externalCode, int $customerId): ?OrderEntity
    {
        $order = $this->doFetch('
          SELECT id, amount_net, amount_gross, amount_tax, duration, external_code, state, external_comment, internal_comment, invoice_number, invoice_url, delivery_address_id, customer_id, company_id, debtor_person_id, debtor_external_data_id, payment_id, created_at, updated_at 
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
            ->setDuration($order['duration'])
            ->setAmountNet($order['amount_net'])
            ->setAmountGross($order['amount_gross'])
            ->setAmountTax($order['amount_tax'])
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
            ->setUpdatedAt(new \DateTime($order['updated_at']));
    }

    public function update(OrderEntity $order): void
    {
        $this->doUpdate('
            UPDATE orders
            SET
              state = :state,
              company_id = :company_id,
              amount_gross = :amount_gross,
              amount_net = :amount_net,
              amount_tax = :amount_tax,
              duration = :duration
            WHERE id = :id
        ', [
            'amount_gross' => $order->getAmountGross(),
            'amount_net' => $order->getAmountNet(),
            'amount_tax' => $order->getAmountTax(),
            'duration' => $order->getDuration(),
            'state' => $order->getState(),
            'company_id' => $order->getCompanyId(),
            'id' => $order->getId(),
        ]);
        $this->eventDispatcher->dispatch(OrderLifecycleEvent::UPDATED, new OrderLifecycleEvent($order));
    }
}
