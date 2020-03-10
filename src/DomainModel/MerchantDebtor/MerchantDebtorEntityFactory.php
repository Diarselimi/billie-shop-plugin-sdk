<?php

namespace App\DomainModel\MerchantDebtor;

use App\DomainModel\DebtorCompany\DebtorCompany;
use App\Helper\Uuid\UuidGeneratorInterface;

class MerchantDebtorEntityFactory
{
    private $uuidGenerator;

    public function __construct(UuidGeneratorInterface $uuidGenerator)
    {
        $this->uuidGenerator = $uuidGenerator;
    }

    public function createFromDatabaseRow(array $row): MerchantDebtorEntity
    {
        return (new MerchantDebtorEntity())
            ->setId($row['id'])
            ->setMerchantId($row['merchant_id'])
            ->setDebtorId($row['debtor_id'])
            ->setCompanyUuid($row['company_uuid'])
            ->setUuid($row['uuid'])
            ->setPaymentDebtorId($row['payment_debtor_id'])
            ->setScoreThresholdsConfigurationId($row['score_thresholds_configuration_id'])
            ->setCreatedAt(new \DateTime($row['created_at']))
            ->setUpdatedAt(new \DateTime($row['updated_at']));
    }

    public function create(
        DebtorCompany $debtorCompany,
        string $merchantId,
        string $paymentDebtorId
    ): MerchantDebtorEntity {
        $now = new \DateTime();

        return (new MerchantDebtorEntity())
            ->setMerchantId($merchantId)
            ->setDebtorId($debtorCompany->getId())
            ->setCompanyUuid($debtorCompany->getUuid())
            ->setUuid($this->uuidGenerator->uuid4())
            ->setPaymentDebtorId($paymentDebtorId)
            ->setCreatedAt($now)
            ->setUpdatedAt($now);
    }
}
