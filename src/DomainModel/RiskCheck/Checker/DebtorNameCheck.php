<?php

namespace App\DomainModel\RiskCheck\Checker;

use App\DomainModel\DebtorExternalData\DebtorExternalDataEntity;
use App\DomainModel\Order\OrderContainer;
use App\DomainModel\RiskCheck\CompanyNameComparator;

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

        $attributes = [
            'name_registry' => $nameFromRegistry,
            'name_order' => $nameFromOrder,
        ];

        $result = $this->nameComparator->compareWithCompanyName($nameFromOrder, $nameFromRegistry);
        if (!$result && $this->canCompareWithPerson($order)) {
            $debtorPerson = $order->getDebtorPerson();

            $result = $this->nameComparator->compareWithPersonName(
                $nameFromRegistry,
                $debtorPerson->getFirstName(),
                $debtorPerson->getLastName()
            );

            $attributes['person_first_name'] = $debtorPerson->getFirstName();
            $attributes['person_last_name'] = $debtorPerson->getLastName();
        }

        return new CheckResult($result, self::NAME, $attributes);
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
