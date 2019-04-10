<?php

namespace spec\App\Helper\Hasher;

use App\DomainModel\DebtorCompany\IdentifyDebtorRequestDTO;
use App\Helper\Hasher\ArrayHasherInterface;
use App\Helper\Hasher\HasherInterface;
use PhpSpec\ObjectBehavior;

class ArrayHasherSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(ArrayHasherInterface::class);
    }

    public function it_returns_the_hash_after_giving_the_object()
    {
        $debtorRequest = new IdentifyDebtorRequestDTO();
        $debtorRequest->setLegalForm("test")
            ->setIsExperimental("test")
            ->setName("randomword");

        $this->generateHash($debtorRequest, ['is_experimental', 'legal_form'])
            ->shouldBeEqualTo(md5("randomword"));
    }

    public function it_should_return_the_correct_hash_also_with_special_characters()
    {
        $debtorRequest = new IdentifyDebtorRequestDTO();
        $debtorRequest
            ->setLegalForm("test")
            ->setIsExperimental("test")
            ->setName("randomword")
            ->setLastName("randomword .randomword?"); //special chars in the last name

        $this->generateHash($debtorRequest, ['is_experimental', 'legal_form'])
            ->shouldBeEqualTo(md5("randomwordrandomwordrandomword"));
    }

    public function it_should_return_the_correct_hash_with_special_chars_and_spaces_()
    {
        $debtorRequest = new IdentifyDebtorRequestDTO();
        $debtorRequest
            ->setLegalForm("test")
            ->setIsExperimental("test")
            ->setPostalCode("123")
            ->setName("rand omwor d")
            ->setLastName("Rrandomword .RA"); //special chars in the last name

        $this->generateHash($debtorRequest, ['is_experimental', 'legal_form'])
            ->shouldBeEqualTo(md5("randomword123rrandomwordra"));
    }

    public function it_should_return_the_correct_hash_with_german_characters_()
    {
        $debtorRequest = new IdentifyDebtorRequestDTO();
        $debtorRequest
            ->setLegalForm("test")
            ->setIsExperimental("test")
            ->setPostalCode("123")
            ->setName("Jürgen")
            ->setLastName("Rrändömßord .Ré"); //special chars in the last name

        $this->generateHash($debtorRequest, ['is_experimental', 'legal_form'])
            ->shouldBeEqualTo(md5("jürgen123rrändömßordré"));
    }
}
