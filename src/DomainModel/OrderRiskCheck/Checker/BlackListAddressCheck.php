<?php

declare(strict_types=1);

namespace App\DomainModel\OrderRiskCheck\Checker;

use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\OrderRiskCheck\CheckResult;
use Ozean12\CompanyStrictMatcher\Address\AddressDTO;
use Ozean12\CompanyStrictMatcher\Address\AddressDTOFactory;
use Ozean12\CompanyStrictMatcher\Address\AddressMatch\AddressMatchService;

class BlackListAddressCheck implements CheckInterface
{
    public const NAME = 'black_listed_address';

    private AddressMatchService $addressMatchService;

    private AddressDTOFactory $addressDTOFactory;

    private array

 $blacklistedAddresses;

    public function __construct(
        AddressMatchService $addressMatchService,
        AddressDTOFactory $addressDTOFactory,
        array $blacklistedAddresses
    ) {
        $this->addressMatchService = $addressMatchService;
        $this->addressDTOFactory = $addressDTOFactory;
        $this->blacklistedAddresses = $blacklistedAddresses;
    }

    public function check(OrderContainer $orderContainer): CheckResult
    {
        $addressFromOrder = $this->addressDTOFactory->createFromArray(
            array_map(fn ($v) => trim($v ?? ''), $orderContainer->getDeliveryAddress()->toArray())
        );

        $addressIsBlackListed = $this->checkIfBlacklisted($addressFromOrder);

        return new CheckResult(!$addressIsBlackListed, self::NAME);
    }

    private function checkIfBlacklisted(AddressDTO $addressFromOrder): bool
    {
        $badAddresses = $this->getBlacklistedAddresses();
        foreach ($badAddresses as $badAddress) {
            if ($this->addressMatchService->matches($addressFromOrder, $badAddress)) {
                return true;
            }
        }

        return false;
    }

    private function getBlacklistedAddresses(): array
    {
        $badAddresses = array_map(function (array $address) {
            return $this->addressDTOFactory->createFromArray($address);
        }, $this->blacklistedAddresses);

        return $badAddresses;
    }
}
