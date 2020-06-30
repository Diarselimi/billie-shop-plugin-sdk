<?php

namespace App\DomainModel\MerchantUser;

interface MerchantUserRepositoryInterface
{
    public function create(MerchantUserEntity $merchantUserEntity): void;

    public function getOneByUuid(string $uuid): ?MerchantUserEntity;

    public function getOneByIdentityVerificationCaseUuid(string $caseUuid): ?MerchantUserEntity;

    public function assignSignatoryPowerToUser(int $id, string $signatoryPowerUuid): void;

    public function assignIdentityVerificationCaseToUser(int $id, string $identityVerificationCaseUuid): void;
}
