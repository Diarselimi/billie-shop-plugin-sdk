<?php

namespace spec\App\DomainModel\OrderRiskCheck\Checker;

use App\DomainModel\Address\AddressEntity;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\OrderRiskCheck\Checker\BlackListAddressCheck;
use Ozean12\CompanyStrictMatcher\Address\AddressDTOFactory;
use Ozean12\CompanyStrictMatcher\Address\AddressMatch\AddressMatchService;
use Ozean12\CompanyStrictMatcher\Address\AddressMatch\HouseNumberMatcher;
use Ozean12\CompanyStrictMatcher\Address\AddressMatch\PostalCodeMatcher;
use Ozean12\CompanyStrictMatcher\Address\AddressMatch\StreetMatcher;
use PhpSpec\ObjectBehavior;
use Psr\Log\NullLogger;

class BlackListAddressCheckSpec extends ObjectBehavior
{
    public function let()
    {
        $addressMatchService = new AddressMatchService(
            new StreetMatcher(),
            new HouseNumberMatcher(),
            new PostalCodeMatcher()
        );
        $this->beConstructedWith($addressMatchService, new AddressDTOFactory(), $this->getBlacklistedAddresses());
        $addressMatchService->setLogger(new NullLogger());
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(BlackListAddressCheck::class);
    }

    public function it_should_pass_with_non_black_listed_address(
        OrderContainer $orderContainer
    ) {
        $address = (new AddressEntity())
            ->setStreet('Charlottestr.')
            ->setHouseNumber('4')
            ->setCity('Berlin')
            ->setPostalCode('10969')
            ->setCountry('DE');

        $orderContainer->getDeliveryAddress()->willReturn($address);

        //ASSERT
        $this->check($orderContainer)->isPassed()->shouldBe(true);
    }

    public function it_should_fail_if_the_address_is_blacklisted(
        OrderContainer $orderContainer
    ) {
        $address = (new AddressEntity())
            ->setStreet('Zugstr.')
            ->setHouseNumber('28')
            ->setCity('Essen')
            ->setPostalCode('45357')
            ->setCountry('DE');

        //* Kohlbrandstr. 24, Frankfurt am Main
        $address1 = (new AddressEntity())
            ->setStreet('Kohlbrandstr.')
            ->setHouseNumber('24')
            ->setCity('Frankfurt am Main')
            ->setPostalCode('60385')
            ->setCountry('DE');

        //* Paapsandstr. 15, 26723 Emden
        $address2 = (new AddressEntity())
            ->setStreet('Paapsandstr.')
            ->setHouseNumber('15')
            ->setCity('Emden')
            ->setPostalCode('26723')
            ->setCountry('DE');

        $orderContainer->getDeliveryAddress()->willReturn($address);
        $this->check($orderContainer)->isPassed()->shouldBe(false);

        $orderContainer->getDeliveryAddress()->willReturn($address1);
        $this->check($orderContainer)->isPassed()->shouldBe(false);

        $orderContainer->getDeliveryAddress()->willReturn($address2);
        $this->check($orderContainer)->isPassed()->shouldBe(false);
    }

    public function it_should_fail_with_similar_addresses_to_blacklisted_ones(OrderContainer $orderContainer)
    {
        $address = (new AddressEntity())
            ->setStreet('Zugstrasse.     ')
            ->setHouseNumber('28   ')
            ->setCity('    Essen   ')
            ->setPostalCode('45537')
            ->setCountry('DE');

        //* Kohlbrandstr. 24, Frankfurt am Main
        $address1 = (new AddressEntity())
            ->setStreet('     Kohlbrandstr     ')
            ->setHouseNumber('   24')
            ->setCity('Frankfurt   ')
            ->setPostalCode('60385    ')
            ->setCountry('DE');

        //* Paapsandstr. 15, 26723 Emden
        $address2 = (new AddressEntity())
            ->setStreet('Paapsandstrase')
            ->setHouseNumber('13-16')
            ->setCity('Emden    ')
            ->setPostalCode('   26723  ')
            ->setCountry('DE');

        $orderContainer->getDeliveryAddress()->willReturn($address);
        $this->check($orderContainer)->isPassed()->shouldBe(false);

        $orderContainer->getDeliveryAddress()->willReturn($address1);
        $this->check($orderContainer)->isPassed()->shouldBe(false);

        $orderContainer->getDeliveryAddress()->willReturn($address2);
        $this->check($orderContainer)->isPassed()->shouldBe(false);
    }

    private function getBlacklistedAddresses(): array
    {
        return [
            [
                'street' => 'Zugstr.',
                'house' => '28',
                'postal_code' => '45357',
                'city' => 'Essen',
            ],
            [
                'street' => 'Kohlbrandstr.',
                'house' => '24',
                'postal_code' => '60385',
                'city' => 'Frankfurt am Main',
            ],
            [
                'street' => 'Paapsandstr.',
                'house' => '15',
                'postal_code' => '26723',
                'city' => 'Emden',
            ],
            [
                'street' => 'Waldstr.',
                'house' => '31',
                'postal_code' => '64625',
                'city' => 'Bensheim',
            ],
        ];
    }
}
