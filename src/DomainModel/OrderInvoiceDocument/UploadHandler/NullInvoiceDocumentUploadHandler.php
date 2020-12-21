<?php

declare(strict_types=1);

namespace App\DomainModel\OrderInvoiceDocument\UploadHandler;

use App\DomainModel\MerchantSettings\MerchantSettingsEntity;
use App\DomainModel\Order\OrderEntity;

class NullInvoiceDocumentUploadHandler extends AbstractInvoiceDocumentUploadHandler
{
    protected const SUPPORTED_STRATEGY = MerchantSettingsEntity::INVOICE_HANDLING_STRATEGY_NONE;

    public function handle(
        OrderEntity $order,
        string $invoiceUuid,
        string $invoiceUrl,
        string $invoiceNumber,
        string $eventSource
    ): void {
        return;
    }
}
