<?php

namespace App\DomainModel\MerchantDebtor\Details;

interface MerchantDebtorDetailsRepositoryInterface
{
    public function getMerchantDebtorDetails(int $merchantDebtorId): MerchantDebtorDetailsDTO;
}
