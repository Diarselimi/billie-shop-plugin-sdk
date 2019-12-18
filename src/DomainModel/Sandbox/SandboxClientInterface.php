<?php

namespace App\DomainModel\Sandbox;

use App\DomainModel\Merchant\MerchantWithCompanyCreationDTO;
use App\DomainModel\MerchantUser\GetMerchantCredentialsDTO;

interface SandboxClientInterface
{
    public function createMerchant(MerchantWithCompanyCreationDTO $creationDTO): SandboxMerchantDTO;

    public function getMerchantCredentials(string $paymentMerchantUuid): ?GetMerchantCredentialsDTO;
}
