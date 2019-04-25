<?php

namespace App\DomainModel\OrderInvoice;

use App\DomainModel\MerchantSettings\MerchantSettingsRepositoryInterface;

abstract class AbstractSettingsAwareInvoiceUploadHandler implements InvoiceUploadHandlerInterface
{
    protected const SUPPORTED_STRATEGY = null;

    private $merchantSettingsRepository;

    public function __construct(MerchantSettingsRepositoryInterface $merchantSettingsRepository)
    {
        $this->merchantSettingsRepository = $merchantSettingsRepository;
    }

    public function supports(
        string $orderExternalCode,
        int $merchantId,
        string $invoiceNumber,
        string $invoiceUrl
    ): bool {
        $settings = $this->merchantSettingsRepository->getOneByMerchant($merchantId);

        return $settings->getInvoiceHandlingStrategy() === static::SUPPORTED_STRATEGY;
    }
}
