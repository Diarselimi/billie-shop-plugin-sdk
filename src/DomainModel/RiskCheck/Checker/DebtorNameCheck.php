<?php

namespace App\DomainModel\RiskCheck\Checker;

use App\DomainModel\Order\OrderContainer;
use App\DomainModel\RiskCheck\CompanyNameComparator;

class DebtorNameCheck implements CheckInterface
{
    public const NAME = 'debtor_name';
    private const LEGAL_FORMS_FOR_PERSON_COMPARISON = ['6022', '2001, 2018, 2022', '4001', '4022', '3001'];

    private $nameComparator;

    public function __construct(CompanyNameComparator $nameComparator)
    {
        $this->nameComparator = $nameComparator;
    }

    public function check(OrderContainer $order): CheckResult
    {
        $nameFromRegistry = $order->getDebtorCompany()->getName();
        $nameFromOrder = $order->getDebtorExternalData()->getName();

        $result = $this->nameComparator->compareWithCompanyName($nameFromOrder, $nameFromRegistry);
        $attributes = [
            'name_registry' => $nameFromRegistry,
            'name_order' => $nameFromOrder,
        ];

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
        return \in_array($order->getDebtorExternalData()->getLegalForm(), self::LEGAL_FORMS_FOR_PERSON_COMPARISON, true);
    }
}
