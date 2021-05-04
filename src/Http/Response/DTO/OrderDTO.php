<?php

namespace App\Http\Response\DTO;

use App\DomainModel\ArrayableInterface;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\Http\Response\DTO\Collection\InvoiceDTOCollection;
use App\Support\DateFormat;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="Order", title="Order Entity", type="object", properties={
 *      @OA\Property(property="external_code", type="string", nullable=true, example="C-10123456789-0001"),
 *      @OA\Property(property="uuid", ref="#/components/schemas/UUID"),
 *      @OA\Property(property="state", ref="#/components/schemas/OrderStateV2", example="created"),
 *      @OA\Property(property="decline_reason", ref="#/components/schemas/OrderDeclineReason", nullable=true),
 *      @OA\Property(property="amount", ref="#/components/schemas/TaxedMoney"),
 *      @OA\Property(property="unshipped_amount", ref="#/components/schemas/TaxedMoney"),
 *      @OA\Property(property="duration", ref="#/components/schemas/OrderDuration", example=30),
 *      @OA\Property(property="created_at", ref="#/components/schemas/DateTime"),
 *      @OA\Property(property="delivery_address", ref="#/components/schemas/Address"),
 *      @OA\Property(property="debtor", ref="#/components/schemas/Debtor"),
 *      @OA\Property(
 *          property="invoices",
 *          type="array",
 *          nullable=false,
 *          @OA\Items(ref="#/components/schemas/OrderInvoiceResponse")
 *      )
 * })
 */
class OrderDTO implements ArrayableInterface
{
    private ?string $externalCode;

    private string $uuid;

    private string $state;

    private ?string $declineReason;

    private TaxedMoneyDTO $amount;

    private TaxedMoneyDTO $unshippedAmount;

    private int $duration;

    private AddressDTO $deliveryAddress;

    private \DateTime $createdAt;

    private InvoiceDTOCollection $invoices;

    private DebtorDTO $debtor;

    public function __construct(
        OrderContainer $orderContainer,
        InvoiceDTOCollection $invoices,
        DebtorDTO $debtor
    ) {
        $this->externalCode = $orderContainer->getOrder()->getExternalCode();
        $this->uuid = $orderContainer->getOrder()->getUuid();
        $this->state = $orderContainer->getOrder()->getState();
        $this->declineReason = $orderContainer->getDeclineReason();
        $this->amount = new TaxedMoneyDTO($orderContainer->getOrderFinancialDetails()->getAmountTaxedMoney());
        $this->unshippedAmount = new TaxedMoneyDTO($orderContainer->getOrderFinancialDetails()->getUnshippedAmountTaxedMoney());
        $this->duration = $orderContainer->getOrderFinancialDetails()->getDuration();
        $this->deliveryAddress = new AddressDTO($orderContainer->getDeliveryAddress());
        $this->createdAt = $orderContainer->getOrder()->getCreatedAt();
        $this->invoices = $invoices;
        $this->debtor = $debtor;
    }

    public function toArray(): array
    {
        return [
            'external_code' => $this->externalCode,
            'uuid' => $this->uuid,
            'state' => $this->state,
            'decline_reason' => $this->declineReason,
            'amount' => $this->amount->toArray(),
            'unshipped_amount' => $this->unshippedAmount->toArray(),
            'duration' => $this->duration,
            'created_at' => $this->createdAt->format(DateFormat::FORMAT_YMD_HIS),
            'delivery_address' => $this->deliveryAddress->toArray(),
            'debtor' => $this->debtor->toArray(),
            'invoices' => $this->invoices->toArray(),
        ];
    }
}
