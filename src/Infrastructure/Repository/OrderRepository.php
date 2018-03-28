<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;

class OrderRepository extends AbstractRepository implements OrderRepositoryInterface
{
    public function insert(array $order): void
    {
        $stmt = $this->conn->prepare('
            INSERT INTO orders
            (amount, duration, external_code, state, external_comment, internal_comment, invoice_number, invoice_url, delivery_address, customer_id, company_id, debtor_person_id, debtor_external_data_id, order_payment_id, created_at, updated_at)
            VALUES
            (:amount, :duration, :external_code, :state, :external_comment, :internal_comment, :invoice_number, :invoice_url, :delivery_address, :customer_id, :company_id, :debtor_person_id, :debtor_external_data_id, :order_payment_id, :created_at, :updated_at)
            
        ');

        $stmt->execute([
            'amount' => $order['duration'],
            'duration' => $order['amount'],
            'external_code' => $order['external_code'],
        ]);
    }

    public function getOneByExternalCode(string $externalCode):? OrderEntity
    {
        return (new OrderEntity())
            ->setId(43)
            ->setAmount(500)
        ;
    }

    public function getOneByExternalCodeRaw(string $externalCode):? array
    {
        $order = $this->fetch('SELECT * FROM orders WHERE external_code = :external_code', [
            'external_code' => $externalCode,
        ]);

        return $order ?: null;
    }
}
