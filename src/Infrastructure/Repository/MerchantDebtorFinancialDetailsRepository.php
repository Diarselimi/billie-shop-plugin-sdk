<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\Merchant\MerchantDebtorFinancialDetailsRepositoryInterface;
use App\DomainModel\MerchantDebtor\MerchantDebtorFinancialDetailsEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorFinancingDetailsEntityFactory;
use Billie\PdoBundle\Infrastructure\Pdo\AbstractPdoRepository;

class MerchantDebtorFinancialDetailsRepository extends AbstractPdoRepository implements MerchantDebtorFinancialDetailsRepositoryInterface
{
    public const TABLE_NAME = "merchant_debtor_financial_details";

    private const SELECT_FIELDS = 'id, merchant_debtor_id, financing_limit, financing_power, created_at';

    private $factory;

    public function __construct(MerchantDebtorFinancingDetailsEntityFactory $factory)
    {
        $this->factory = $factory;
    }

    public function insert(MerchantDebtorFinancialDetailsEntity $financialDetails): void
    {
        $financialDetails->setCreatedAt(new \DateTime());

        $id = $this->doInsert('
            INSERT INTO '.self::TABLE_NAME.'
            (merchant_debtor_id, financing_limit, financing_power, created_at)
            VALUES
            (:merchant_debtor_id, :financing_limit, :financing_power, :created_at)
        ', [
            'merchant_debtor_id' => $financialDetails->getMerchantDebtorId(),
            'financing_limit' => $financialDetails->getFinancingLimit(),
            'financing_power' => $financialDetails->getFinancingPower(),
            'created_at' => $financialDetails->getCreatedAt()->format(self::DATE_FORMAT),
        ]);

        $financialDetails->setId($id);
    }

    public function getCurrentByMerchantDebtorId(int $merchantDebtorId): ?MerchantDebtorFinancialDetailsEntity
    {
        $row = $this->doFetchOne('
          SELECT ' . self::SELECT_FIELDS . '
          FROM '.self::TABLE_NAME.'
          WHERE merchant_debtor_id = :merchant_debtor_id
          ORDER BY id DESC
          LIMIT 1
        ', ['merchant_debtor_id' => $merchantDebtorId]);

        return $row ? $this->factory->createFromDatabaseRow($row) : null;
    }
}
