<?php

namespace App\DomainModel\Merchant;

interface MerchantRepositoryInterface
{
    public function insert(MerchantEntity $merchant): void;
    public function update(MerchantEntity $merchant): void;
    public function getOneById(int $id): ?MerchantEntity;
    public function getOneByApiKeyRaw(string $apiKey): ?array;
}
