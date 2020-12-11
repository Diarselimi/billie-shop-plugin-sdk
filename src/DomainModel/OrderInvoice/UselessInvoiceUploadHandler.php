<?php

namespace App\DomainModel\OrderInvoice;

use App\DomainModel\MerchantSettings\MerchantSettingsEntity;
use App\DomainModel\Order\OrderEntity;

class UselessInvoiceUploadHandler extends AbstractSettingsAwareInvoiceUploadHandler
{
    protected const SUPPORTED_STRATEGY = MerchantSettingsEntity::INVOICE_HANDLING_STRATEGY_NONE;

    public function handleInvoice(OrderEntity $order, string $invoiceUrl, string $invoiceNumber, string $event): void
    {
    }
}
