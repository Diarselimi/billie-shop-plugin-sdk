<?php

declare(strict_types=1);

use App\DomainModel\Iban\IbanFraudCheck;
use App\DomainModel\OrderRiskCheck\Checker\AmountCheck;
use App\DomainModel\OrderRiskCheck\Checker\AvailableFinancingLimitCheck;
use App\DomainModel\OrderRiskCheck\Checker\DebtorCountryCheck;
use App\DomainModel\OrderRiskCheck\Checker\DebtorIdentifiedCheck;
use App\DomainModel\OrderRiskCheck\Checker\DebtorIndustrySectorCheck;
use App\DomainModel\OrderRiskCheck\Checker\DebtorNotCustomerCheck;
use App\DomainModel\OrderRiskCheck\Checker\DebtorScoreCheck;
use App\DomainModel\OrderRiskCheck\Checker\LimitCheck;
use Phinx\Seed\AbstractSeed;

class Seed001AddRiskCheckDefinitions extends AbstractSeed
{
    public function run()
    {
        $riskCheckDefinitions = [
            AvailableFinancingLimitCheck::NAME,
            AmountCheck::NAME,
            DebtorCountryCheck::NAME,
            DebtorIndustrySectorCheck::NAME,
            DebtorIdentifiedCheck::NAME,
            LimitCheck::NAME,
            DebtorNotCustomerCheck::NAME,
            DebtorScoreCheck::NAME,
            IbanFraudCheck::RISK_CHECK_NAME,
        ];

        foreach ($riskCheckDefinitions as $riskCheckDefinitionName) {
            $this
                ->table('risk_check_definitions')
                ->insert(
                    [
                        'name' => $riskCheckDefinitionName,
                        'created_at' => (new DateTime())->format('Y-m-d H:i:s'),
                        'updated_at' => (new DateTime())->format('Y-m-d H:i:s'),
                    ]
                )
                ->save();
        }
    }
}
