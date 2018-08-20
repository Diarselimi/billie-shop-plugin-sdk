<?php

namespace Tests\src\DomainModel\RiskCheck;

use App\DomainModel\RiskCheck\CompanyNameComparator;
use PHPUnit\Framework\TestCase;

class CompanyNameComparatorTest extends TestCase
{
    /**
     * @var CompanyNameComparator
     */
    private $comparator;

    protected function setUp()
    {
        $this->comparator = new CompanyNameComparator();
    }

    /**
     * Test compare
     * When the company names are similar
     * Then true should be returned
     *
     * @dataProvider successComparisonWithCompanyNameProvider
     */
    public function testCompareWithCompanyNameWhenNamesAreSimilar(string $companyName1, string $companyName2)
    {
        $result = $this->comparator->compareWithCompanyName($companyName1, $companyName2);

        $this->assertTrue($result);
    }

    public function successComparisonWithCompanyNameProvider(): array
    {
        return [
            ['', ''],
            ['a', 'a'],
            ['company', 'company'],
            ['company', 'COMPANY'],
            ['company gmbh', 'company'],
            ['company gmbh', 'company GmbH'],
            ['company', 'company GMBH'],
            ['company e. v. aaa', 'company aaa'],
            ['company &co.kg', 'company ltd. & co. kg'],
            ['company', 'company & co. kg'],
            ['company ltd. & co. kg', 'company'],
            ['com&any', 'com&pany'],
            ['com&pany', 'com&any'],
            ['three words name', 'three words name'],
            ['three words', 'three words name'],
            ['three', 'three words name'],
            ['four words name company', 'four words different name'],
            ['four words name company', 'four words a bit different names'],
            ['four w0rds n4mes company', 'four words a bit different names'],
            ['four w0rds ug (haftungsbeschränkt) n4mes company', 'four words a bit different names'],
            ['four w0rds ug (haftungsbeschränkt) n4mes company', 'four words a bit different names eg&co.kgaa'],
            ['newlegal GmbH&Co.KG', 'newlegal GmbH'],
            ['someßcompany', 'somesscompany'],
            ['company gesellschaft', 'company gmbh'],
            ['companygesellschaft', 'company gmbh'],

            ['Marktgemeinde Bruckmühl - Bauhof', 'Marktgemeinde Bruckmühl'],
            ['RWTH Aachen University', 'Rheinisch-Westfälisch Technische Hochschule (RWTH) Aachen'],
        ];
    }

    /**
     * Test compare
     * When the company names are different
     * Then false should be returned
     *
     * @dataProvider failureComparisonWithCompanyNameProvider
     */
    public function testCompareWithCompanyNameWhenNamesAreDifferent(string $companyName1, string $companyName2)
    {
        $result = $this->comparator->compareWithCompanyName($companyName1, $companyName2);

        $this->assertFalse($result);
    }

    public function failureComparisonWithCompanyNameProvider(): array
    {
        return [
            ['a', ''],
            ['', 'b'],
            ['a', 'b'],
            ['com', 'company'],
            ['any company GmbH', 'bit different company name GmbH'],
            ['any longer company name GmbH', 'bit longer different c0mp4ny name GmbH'],
        ];
    }

    /**
     * Test compare
     * When the company names and person name are different
     * Then false should be returned
     *
     * @dataProvider failureComparisonWithPersonNameProvider
     */
    public function testCompareWithPersonNameWhenNamesAreDifferent(string $companyName, string $personFirstName, string $personLastName)
    {
        $result = $this->comparator->compareWithPersonName($companyName, $personFirstName, $personLastName);

        $this->assertFalse($result);
    }

    public function failureComparisonWithPersonNameProvider(): array
    {
        return [
            ['', 'first', ''],
            ['', '', 'last'],
            ['Frst Last', 'First', 'Last'],
            ['First Last', 'First', 'Lat'],
        ];
    }

    /**
     * Test compare
     * When the company names and person name are similar
     * Then true should be returned
     *
     * @dataProvider successComparisonWithPersonNameProvider
     */
    public function testCompareWithPersonNameWhenNamesAreSimilar(string $companyName, string $personFirstName, string $personLastName)
    {
        $result = $this->comparator->compareWithPersonName($companyName, $personFirstName, $personLastName);

        $this->assertTrue($result);
    }

    public function successComparisonWithPersonNameProvider(): array
    {
        return [
            ['', '', ''],
            ['First Last', 'First', 'Last'],
            ['first last', 'First', 'Last'],
            ['FIRST Last', 'first', 'last'],
            ['First Middle Last', 'First', 'Last'],
            ['First Middle Last', 'middle', 'last'],
            ['Prefix First Middle Last Suffix', 'First', 'Last'],
        ];
    }
}
