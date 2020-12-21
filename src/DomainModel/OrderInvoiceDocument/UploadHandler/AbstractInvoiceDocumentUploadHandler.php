<?php

declare(strict_types=1);

namespace App\DomainModel\OrderInvoiceDocument\UploadHandler;

use App\DomainModel\MerchantSettings\MerchantSettingsNotFoundException;
use App\DomainModel\MerchantSettings\MerchantSettingsRepositoryInterface;

abstract class AbstractInvoiceDocumentUploadHandler implements InvoiceDocumentUploadHandlerInterface
{
    protected const SUPPORTED_STRATEGY = null;

    private MerchantSettingsRepositoryInterface $merchantSettingsRepository;

    public function __construct(MerchantSettingsRepositoryInterface $merchantSettingsRepository)
    {
        $this->merchantSettingsRepository = $merchantSettingsRepository;
    }

    public function supports(int $merchantId): bool
    {
        $settings = $this->merchantSettingsRepository->getOneByMerchant($merchantId);

        if ($settings === null) {
            throw new MerchantSettingsNotFoundException();
        }

        return $settings->getInvoiceHandlingStrategy() === static::SUPPORTED_STRATEGY;
    }
}
