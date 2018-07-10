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
     * @dataProvider successComparisonProvider
     */
    public function testCompareWhenCompanyNamesAreSimilar(string $companyName1, string $companyName2)
    {
        $result = $this->comparator->compare($companyName1, $companyName2);

        $this->assertTrue($result);
    }

    public function successComparisonProvider()
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
        ];
    }

    /**
     * Test compare
     * When the company names are different
     * Then false should be returned
     *
     * @dataProvider failureComparisonProvider
     */
    public function testCompareWhenCompanyNamesAreDifferent(string $companyName1, string $companyName2)
    {
        $result = $this->comparator->compare($companyName1, $companyName2);

        $this->assertFalse($result);
    }

    public function failureComparisonProvider()
    {
        return [
            ['a', ''],
            ['', 'b'],
            ['a', 'b'],
            ['com', 'company'],
            ['any company name GmbH', 'bit different company name GmbH'],
            ['any longer company name GmbH', 'bit longer different c0mp4ny name GmbH'],
        ];
    }
}
