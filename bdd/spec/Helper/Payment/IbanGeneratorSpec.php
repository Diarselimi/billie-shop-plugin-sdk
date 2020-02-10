<?php

namespace spec\App\Helper\Payment;

use App\Helper\Payment\IbanGenerator;
use PhpSpec\ObjectBehavior;
use Webmozart\Assert\Assert;

class IbanGeneratorSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(IbanGenerator::class);
    }

    public function it_generatesiban()
    {
        $testCases = [
            ['DE', null, 24],
            ['NL', null, 24],
            ['AT', null, 24],
        ];

        foreach ($testCases as $testCase) {
            $iban = $this->iban(...$testCase);
            $iban->shouldBeString();
            $iban->shouldContain($testCase[0]);

            $ibanObject = new \IBAN($iban->getWrappedObject());
            Assert::true($ibanObject->Verify());
        }
    }

    public function it_generates_bic()
    {
        $this->bic()->shouldBe('RANDOMBICXX');
    }
}
