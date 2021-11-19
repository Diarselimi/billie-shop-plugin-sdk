<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\DomainModel\Invoice\ShippingInfo;
use App\DomainModel\Invoice\ShippingInfo\ShippingInfoRepository;
use Billie\PdoBundle\Infrastructure\Pdo\AbstractPdoRepository;

class ShippingInfoPdoRepository extends AbstractPdoRepository implements ShippingInfoRepository
{
    private const TABLE_NAME = 'shipping_infos';

    private const SELECT_FIELDS = [
        'invoice_uuid',
        'return_shipping_company',
        'return_tracking_number',
        'return_tracking_url',
        'shipping_company',
        'shipping_method',
        'tracking_number',
        'tracking_url',
        'created_at',
        'updated_at',
    ];

    public function save(ShippingInfo $shippingInfo): void
    {
        $timeStamp = new \DateTime();

        $this->doInsert(
            'INSERT INTO ' .self::TABLE_NAME .
            ' (' . implode(', ', self::SELECT_FIELDS) . ') VALUES (' .
            ':' . implode(',:', self::SELECT_FIELDS) . ')',
            [
                'invoice_uuid' => $shippingInfo->getInvoiceUuid(),
                'tracking_url' => $shippingInfo->getTrackingUrl(),
                'tracking_number' => $shippingInfo->getTrackingNumber(),
                'return_shipping_company' => $shippingInfo->getReturnShippingCompany(),
                'return_tracking_number' => $shippingInfo->getReturnTrackingNumber(),
                'return_tracking_url' => $shippingInfo->getReturnTrackingUrl(),
                'shipping_company' => $shippingInfo->getShippingCompany(),
                'shipping_method' => $shippingInfo->getShippingMethod(),
                'updated_at' => $timeStamp->format(self::DATE_FORMAT),
                'created_at' => $timeStamp->format(self::DATE_FORMAT),
            ]
        );
    }
}
