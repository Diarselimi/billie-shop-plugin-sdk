<?php

namespace App\Tests\src\DomainModel\RiskCheck;

use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\DebtorExternalData\DebtorExternalDataEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\Order\OrderContainer;
use App\DomainModel\Person\PersonEntity;
use App\DomainModel\RiskCheck\Checker\DebtorNameCheck;
use App\DomainModel\RiskCheck\CompanyNameComparator;
use PHPUnit\Framework\TestCase;

class DebtorNameCheckTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CompanyNameComparator
     */
    private $comparator;

    /**
     * @var DebtorNameCheck
     */
    private $check;

    public function setUp()
    {
        $this->check = new DebtorNameCheck(
            $this->comparator = $this->createMock(CompanyNameComparator::class)
        );
    }

    /**
     * Test check
     * When the legal form is not a sole trader
     * Then we should compare by company name
     *
     * @dataProvider compareByCompanyNameProvider
     */
    public function testCompareByCompanyName(
        string $nameFromOrder,
        string $nameFromRegistry,
        string $legalForm,
        bool $expectedResult
    ) {
        $this->comparator->expects($this->never())->method('compareWithPersonName');
        $this->comparator->expects($this->once())->method('compareWithCompanyName')
            ->with($nameFromOrder, $nameFromRegistry)
            ->willReturn($expectedResult)
        ;

        $order = (new OrderContainer())
            ->setMerchantDebtor((new MerchantDebtorEntity())->setDebtorCompany((new DebtorCompany())->setName($nameFromRegistry)))
            ->setDebtorExternalData((new DebtorExternalDataEntity())->setName($nameFromOrder)->setLegalForm($legalForm))
        ;
        $result = $this->check->check($order);

        $this->assertEquals($expectedResult, $result->isPassed());
    }

    public function compareByCompanyNameProvider(): array
    {
        return [
            ['order name', 'registry name', '6023', true],
            ['order name', 'registry name', '2001, 2018, 2021', true],
        ];
    }

    /**
     * Test check
     * When the legal form is a sole trader
     * Then we should compare by company name and by person
     *
     * @dataProvider compareByCompanyAndPersonNameProvider
     */
    public function testCompareByCompanyAndPersonName(
        string $nameFromOrder,
        string $nameFromRegistry,
        string $personFirstName,
        string $personLastName,
        string $legalForm,
        bool $expectedResult
    ) {
        $this->comparator->expects($this->once())->method('compareWithPersonName')
            ->with($nameFromRegistry, $personFirstName, $personLastName)
            ->willReturn($expectedResult)
        ;
        $this->comparator->expects($this->once())->method('compareWithCompanyName')->willReturn(false);

        $order = (new OrderContainer())
            ->setMerchantDebtor((new MerchantDebtorEntity())->setDebtorCompany((new DebtorCompany())->setName($nameFromRegistry)))
            ->setDebtorExternalData((new DebtorExternalDataEntity())->setName($nameFromOrder)->setLegalForm($legalForm))
            ->setDebtorPerson((new PersonEntity())->setFirstName($personFirstName)->setLastName($personLastName))
        ;
        $result = $this->check->check($order);

        $this->assertEquals($expectedResult, $result->isPassed());
    }

    public function compareByCompanyAndPersonNameProvider(): array
    {
        return [
            ['order name', 'registry name', 'First', 'Last', '6022', true],
            ['order name', 'registry name', 'First', 'Last', '6022', false],
            ['order name', 'registry name', 'First', 'Last', '2001, 2018, 2022', true],
            ['order name', 'registry name', 'First', 'Last', '2001, 2018, 2022', false],
        ];
    }
}
