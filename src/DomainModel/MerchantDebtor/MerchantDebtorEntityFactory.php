<?php

namespace App\DomainModel\MerchantDebtor;

use App\DomainModel\DebtorCompany\DebtorCompany;
use App\Helper\Uuid\UuidGeneratorInterface;
use App\Support\AbstractFactory;

class MerchantDebtorEntityFactory extends AbstractFactory
{
    private $uuidGenerator;

    public function __construct(UuidGeneratorInterface $uuidGenerator)
    {
        $this->uuidGenerator = $uuidGenerator;
    }

    public function createFromArray(array $data): MerchantDebtorEntity
    {
        return (new MerchantDebtorEntity())
            ->setId($data['id'])
            ->setMerchantId($data['merchant_id'])
            ->setDebtorId($data['debtor_id'])
            ->setCompanyUuid($data['company_uuid'])
            ->setUuid($data['uuid'])
            ->setPaymentDebtorId($data['payment_debtor_id'])
            ->setScoreThresholdsConfigurationId($data['score_thresholds_configuration_id'])
            ->setCreatedAt(new \DateTime($data['created_at']))
            ->setUpdatedAt(new \DateTime($data['updated_at']));
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
