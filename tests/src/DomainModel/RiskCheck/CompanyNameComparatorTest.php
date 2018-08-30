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
            ['four words name company', 'four words a bit different name'],
            ['four w0rds n4mes company', 'four words a bit different names'],
            ['four w0rds ug (haftungsbeschränkt) n4mes company', 'four words a bit different names'],
            ['four w0rds ug (haftungsbeschränkt) n4mes company', 'four words a bit different names eg&co.kgaa'],
            ['newlegal GmbH&Co.KG', 'newlegal GmbH'],
            ['someßcompany', 'somesscompany'],
            ['company gesellschaft', 'company gmbh'],
            ['companygesellschaft', 'company gmbh'],
            // shorter name as second parameter
            ["Company GmbH Suffix", "Company GmbH"],
            // ß
            ["fooßbar", "foossbar"],
            // Umlauts
            ["Fooöbar Bazäqux GmbH & Co. KG", "Foooebar Bazaequx GmbH & Co. KG"],
            // Superfluous whitespace
            ["S O M E - W O R D S  AG", "SOME-WORDS AG"],
            // Superfluous special characters
            ["Words AG", "wo.rds Aktiengesellschaft"],
             // Superfluous parens and two worlds from right match in left
            ["ABC Place Type", "Acronym Be Cool (ABC) Place"],
            // Typos
            ["1234x", "1234y"], // allow one typo in five letter words
            ["12345", "12345x"], // allow superfulous character in five letter words
            ["1234567890", "123x567x90"], // allow Two typos in ten letter words
            ["1234567890", "1234x567x890"], // allow two superfluous letters in ten letter words
            // some real world examples
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
            // Typos
            ["12x4", "12y4"], // allow no typos in 4 letter words
            ["1234567890", "12x45x78x0"], // allow no three typos in ten letter words
            ["1234567890", "1x234x567x890"], // allow no three superfluous letters in ten letter words
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
            ['FIRST LASST', 'First', 'Laßt'],
            // Compount name with dash
            ['First-Middle Last Suffix', 'First Middle', 'Last'],
            ['First Middle Last Suffix', 'First-Middle', 'Last']
        ];
    }
}
