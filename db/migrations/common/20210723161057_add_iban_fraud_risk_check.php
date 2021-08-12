<?php

use App\DomainModel\Iban\IbanFraudCheck;
use App\Infrastructure\Phinx\TransactionalMigration;
use App\Infrastructure\Repository\RiskCheckDefinitionRepository;

class AddIbanFraudRiskCheck extends TransactionalMigration
{
    public function migrate()
    {
        $now = (new \DateTime())->format(\DATE_ATOM);
        $definition = [
            'name' => IbanFraudCheck::RISK_CHECK_NAME,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        $this
            ->table(RiskCheckDefinitionRepository::TABLE_NAME)
            ->insert($definition)
            ->save()
        ;
    }
}
