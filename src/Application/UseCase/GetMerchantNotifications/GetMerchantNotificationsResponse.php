<?php

namespace App\Application\UseCase\GetMerchantNotifications;

use App\DomainModel\ArrayableInterface;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="GetMerchantNotificationsResponse",
 *     title="Merchant Notifications Count",
 *     type="object",
 *     properties={
 *          @OA\Property(property="debtor_information_change_requests", type="integer", nullable=false)
 *     }
 * )
 */
class GetMerchantNotificationsResponse implements ArrayableInterface
{
    private $debtorInformationChangeRequests;

    public function __construct(int $debtorInformationChangeRequests)
    {
        $this->debtorInformationChangeRequests = $debtorInformationChangeRequests;
    }

    public function getDebtorInformationChangeRequests(): int
    {
        return $this->debtorInformationChangeRequests;
    }

    public function toArray(): array
    {
        return [
            'debtor_information_change_requests' => $this->getDebtorInformationChangeRequests(),
        ];
    }
}
