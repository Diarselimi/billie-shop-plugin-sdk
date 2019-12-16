<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\DomainModel\MerchantFinancialAssessment\MerchantFinancialAssessmentRepositoryInterface;
use App\DomainModel\MerchantFinancialAssessment\MerchantFinancialAssessmentEntity;
use App\DomainModel\MerchantFinancialAssessment\MerchantFinancialAssessmentEntityFactory;
use Billie\PdoBundle\Infrastructure\Pdo\AbstractPdoRepository;

class MerchantFinancialAssessmentRepository extends AbstractPdoRepository implements MerchantFinancialAssessmentRepositoryInterface
{
    public const TABLE_NAME = 'merchant_financial_assessments';

    private const SELECT_FIELDS = [
        'id',
        'merchant_id',
        'data',
        'created_at',
        'updated_at',
    ];

    private $factory;

    public function __construct(MerchantFinancialAssessmentEntityFactory $factory)
    {
        $this->factory = $factory;
    }

    public function insert(MerchantFinancialAssessmentEntity $entity): void
    {
        $data = [
            'merchant_id' => $entity->getMerchantId(),
            'data' => json_encode($entity->getData()),
            'created_at' => $entity->getCreatedAt()->format(self::DATE_FORMAT),
            'updated_at' => $entity->getUpdatedAt()->format(self::DATE_FORMAT),
        ];

        $sql = $this->generateInsertQuery(self::TABLE_NAME, array_keys($data));

        $id = $this->doInsert($sql, $data);
        $entity->setId($id);
    }

    public function findOneByMerchant(int $merchantId): ?MerchantFinancialAssessmentEntity
    {
        $sql = $this->generateSelectQuery(self::TABLE_NAME, self::SELECT_FIELDS).
            ' WHERE merchant_id = :merchant_id ORDER BY id DESC LIMIT 1';

        $row = $this->doFetchOne($sql, ['merchant_id' => $merchantId]);

        return $row ? $this->factory->createFromArray($row) : null;
    }
}
