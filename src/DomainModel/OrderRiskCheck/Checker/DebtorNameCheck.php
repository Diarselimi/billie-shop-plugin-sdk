<?php

namespace App\DomainModel\OrderRiskCheck\Checker;

use App\DomainModel\DebtorExternalData\DebtorExternalDataEntity;
use App\DomainModel\Order\OrderContainer;
use App\DomainModel\OrderRiskCheck\CompanyNameComparator;

class DebtorNameCheck implements CheckInterface
{
    public const NAME = 'debtor_name';

    private $nameComparator;

    public function __construct(CompanyNameComparator $nameComparator)
    {
        $this->nameComparator = $nameComparator;
    }

    public function check(OrderContainer $order): CheckResult
    {
        $nameFromRegistry = $order->getMerchantDebtor()->getDebtorCompany()->getName();
        $nameFromOrder = $order->getDebtorExternalData()->getName();

        $result = $this->nameComparator->compareWithCompanyName($nameFromOrder, $nameFromRegistry);
        if (!$result && $this->canCompareWithPerson($order)) {
            $debtorPerson = $order->getDebtorPerson();

            $result = $this->nameComparator->compareWithPersonName(
                $nameFromRegistry,
                $debtorPerson->getFirstName(),
                $debtorPerson->getLastName()
            );
        }

        return new CheckResult($result, self::NAME);
    }

    private function canCompareWithPerson(OrderContainer $order): bool
    {
        return in_array(
            $order->getDebtorExternalData()->getLegalForm(),
            DebtorExternalDataEntity::LEGAL_FORMS_FOR_PERSON_COMPARISON,
            true
        );
    }
}
