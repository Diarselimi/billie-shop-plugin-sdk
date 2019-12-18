<?php

namespace App\DomainModel\Merchant;

interface MerchantRepositoryInterface
{
    public function insert(MerchantEntity $merchant): void;

    public function update(MerchantEntity $merchant): void;

    public function getOneById(int $id): ?MerchantEntity;

    public function getOneByUuid(string $paymentUuid): ?MerchantEntity;

    public function getOneByCompanyId(int $id): ?MerchantEntity;

    public function getOneByApiKey(string $apiKey): ?MerchantEntity;

    public function getOneByOauthClientId(string $oauthClientId): ? MerchantEntity;

    public function findActiveWithFinancingPowerBelowPercentage(float $percentage): ?array;
}
