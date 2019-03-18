<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\OrderIdentification\OrderIdentificationEntity;
use App\DomainModel\OrderIdentification\OrderIdentificationRepositoryInterface;
use Billie\PdoBundle\Infrastructure\Pdo\AbstractPdoRepository;

class OrderIdentificationRepository extends AbstractPdoRepository implements OrderIdentificationRepositoryInterface
{
    public function insert(OrderIdentificationEntity $orderIdentificationEntity): void
    {
        $id = $this->doInsert('
            INSERT INTO order_identifications
            (order_id, v1_company_id, v2_company_id, created_at, updated_at)
            VALUES
            (:order_id, :v1_company_id, :v2_company_id, :created_at, :updated_at)
        ', [
            'order_id' => $orderIdentificationEntity->getOrderId(),
            'v1_company_id' => $orderIdentificationEntity->getV1CompanyId(),
            'v2_company_id' => $orderIdentificationEntity->getV2CompanyId(),
            'created_at' => $orderIdentificationEntity->getCreatedAt()->format(self::DATE_FORMAT),
            'updated_at' => $orderIdentificationEntity->getUpdatedAt()->format(self::DATE_FORMAT),
        ]);

        $orderIdentificationEntity->setId($id);
    }

    public function findOneByOrderAndCompanyIds(int $orderId, int $v1CompanyId, int $v2CompanyId): ? array
    {
        $row = $this->doFetchOne('
          SELECT *
          FROM order_identifications
          WHERE order_id = :order_id And v1_company_id = :v1_company_id And v2_company_id = :v2_company_id
        ', [
            'order_id' => $orderId,
            'v1_company_id' => $v1CompanyId,
            'v2_company_id' => $v2CompanyId,
        ]);

        return $row ? $row : null;
    }
}
