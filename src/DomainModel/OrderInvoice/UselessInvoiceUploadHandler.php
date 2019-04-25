<?php

namespace App\DomainModel\OrderInvoice;

use App\DomainModel\MerchantSettings\MerchantSettingsEntity;

class UselessInvoiceUploadHandler extends AbstractSettingsAwareInvoiceUploadHandler
{
    protected const SUPPORTED_STRATEGY = MerchantSettingsEntity::INVOICE_HANDLING_STRATEGY_NONE;

    public function handleInvoice(
        string $orderExternalCode,
        int $merchantId,
        string $invoiceNumber,
        string $invoiceUrl,
        string $event
    ): void {
        return;
    }
}
