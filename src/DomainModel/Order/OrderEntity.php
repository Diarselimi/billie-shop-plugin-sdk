<?php

namespace App\DomainModel\Order;

use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsEntity;
use App\DomainModel\OrderInvoice\OrderInvoiceCollection;
use Billie\PdoBundle\DomainModel\AbstractTimestampableEntity;
use Billie\PdoBundle\DomainModel\StatefulEntity\StatefulEntityInterface;
use Billie\PdoBundle\DomainModel\StatefulEntity\StatefulEntityTrait;
use Ramsey\Uuid\UuidInterface;

class OrderEntity extends AbstractTimestampableEntity implements StatefulEntityInterface
{
    use StatefulEntityTrait;

    public const STATE_NEW = 'new';

    public const STATE_PRE_WAITING = 'pre_waiting';

    public const STATE_AUTHORIZED = 'authorized';

    public const STATE_WAITING = 'waiting';

    public const STATE_CREATED = 'created';

    public const STATE_DECLINED = 'declined';

    public const STATE_SHIPPED = 'shipped';

    public const STATE_PARTIALLY_SHIPPED = 'partially_shipped';

    public const STATE_PAID_OUT = 'paid_out';

    public const STATE_LATE = 'late';

    public const STATE_COMPLETE = 'complete';

    public const STATE_CANCELED = 'canceled';

    public const TRANSITION_PRE_WAITING = 'pre_waiting';

    public const TRANSITION_AUTHORIZE = 'authorize';

    public const TRANSITION_WAITING = 'waiting';

    public const TRANSITION_CREATE = 'create';

    public const TRANSITION_DECLINE = 'decline';

    public const TRANSITION_PAY_OUT = 'pay_out';

    public const TRANSITION_SHIP = 'ship';

    public const TRANSITION_SHIP_PARTIALLY = 'ship_partially';

    public const TRANSITION_SHIP_FULLY = 'ship_fully';

    public const TRANSITION_LATE = 'late';

    public const TRANSITION_COMPLETE = 'complete';

    public const TRANSITION_CANCEL = 'cancel';

    public const TRANSITION_CANCEL_SHIPPED = 'cancel_shipped';

    public const TRANSITION_CANCEL_WAITING = 'cancel_waiting';

    public const ALL_STATES = [
        self::STATE_NEW,
        self::STATE_PRE_WAITING,
        self::STATE_AUTHORIZED,
        self::STATE_WAITING,
        self::STATE_CREATED,
        self::STATE_DECLINED,
        self::STATE_SHIPPED,
        self::STATE_PAID_OUT,
        self::STATE_LATE,
        self::STATE_COMPLETE,
        self::STATE_CANCELED,
    ];

    public const ALL_STATES_V2 = [
        self::STATE_WAITING,
        self::STATE_CREATED,
        self::STATE_DECLINED,
        self::STATE_PARTIALLY_SHIPPED,
        self::STATE_SHIPPED,
        self::STATE_COMPLETE,
        self::STATE_CANCELED,
        self::STATE_PRE_WAITING,
        self::STATE_AUTHORIZED,
    ];

    public const MAX_DURATION_IN_PRE_WAITING_STATE = '1 days';

    public const MAX_DURATION_IN_AUTHORIZED_STATE = '1 days';

    public const MAX_DURATION_IN_WAITING_STATE = '9 days';

    public const CREATION_SOURCE_API = 'api';

    public const CREATION_SOURCE_CHECKOUT = 'checkout';

    public const CREATION_SOURCE_DASHBOARD = 'dashboard';

    public const WORKFLOW_NAME_V1 = 'order_v1';

    public const WORKFLOW_NAME_V2 = 'order_v2';

    private const STATE_TRANSITION_ENTITY_CLASS = OrderStateTransitionEntity::class;

    private string $uuid;

    /**
     * @deprecated
     */
    private float $amountForgiven;

    private ?string $externalCode = null;

    private ?string $externalComment = null;

    private ?string $internalComment = null;

    /**
     * @deprecated
     */
    private ?string $invoiceNumber = null;

    /**
     * @deprecated
     */
    private ?string $invoiceUrl = null;

    /**
     * @deprecated
     */
    private ?string $proofOfDeliveryUrl = null;

    private ?int $merchantDebtorId = null;

    private int $merchantId;

    private int $deliveryAddressId;

    private int $debtorPersonId;

    private int $debtorExternalDataId;

    /**
     * @deprecated
     */
    private ?string $paymentId = null;

    /**
     * @deprecated probably
     */
    private ?\DateTime $shippedAt = null;

    private ?int $checkoutSessionId = null;

    private string $creationSource;

    private ?string $companyBillingAddressUuid = null;

    private ?UuidInterface $debtorSepaMandateUuid = null;

    private string $workflowName;

    private ?int $durationExtension;

    private OrderFinancialDetailsEntity $latestOrderFinancialDetails;

    private OrderInvoiceCollection $orderInvoices;

    public function __construct()
    {
        parent::__construct();

        $this->orderInvoices = new OrderInvoiceCollection([]);
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): OrderEntity
    {
        $this->uuid = $uuid;

        return $this;
    }

    /** @deprecated */
    public function getAmountForgiven(): float
    {
        return $this->amountForgiven;
    }

    /** @deprecated */
    public function setAmountForgiven(float $amountForgiven): OrderEntity
    {
        $this->amountForgiven = $amountForgiven;

        return $this;
    }

    public function getExternalCode(): ?string
    {
        return $this->externalCode;
    }

    public function setExternalCode(?string $externalCode): OrderEntity
    {
        $this->externalCode = $externalCode;

        return $this;
    }

    public function getExternalComment(): ?string
    {
        return $this->externalComment;
    }

    public function setExternalComment(?string $externalComment): OrderEntity
    {
        $this->externalComment = $externalComment;

        return $this;
    }

    public function getInternalComment(): ?string
    {
        return $this->internalComment;
    }

    public function setInternalComment(?string $internalComment): OrderEntity
    {
        $this->internalComment = $internalComment;

        return $this;
    }

    public function getInvoiceNumber(): ?string
    {
        return $this->invoiceNumber;
    }

    public function setInvoiceNumber(?string $invoiceNumber): OrderEntity
    {
        $this->invoiceNumber = $invoiceNumber;

        return $this;
    }

    public function getInvoiceUrl(): ?string
    {
        return $this->invoiceUrl;
    }

    public function setInvoiceUrl(?string $invoiceUrl): OrderEntity
    {
        $this->invoiceUrl = $invoiceUrl;

        return $this;
    }

    public function getProofOfDeliveryUrl(): ?string
    {
        return $this->proofOfDeliveryUrl;
    }

    public function setProofOfDeliveryUrl(?string $proofOfDeliveryUrl): OrderEntity
    {
        $this->proofOfDeliveryUrl = $proofOfDeliveryUrl;

        return $this;
    }

    public function getMerchantDebtorId(): ?int
    {
        return $this->merchantDebtorId;
    }

    public function setMerchantDebtorId(?int $merchantDebtorId): OrderEntity
    {
        $this->merchantDebtorId = $merchantDebtorId;

        return $this;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function setMerchantId(int $merchantId): OrderEntity
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    public function getDeliveryAddressId(): int
    {
        return $this->deliveryAddressId;
    }

    public function setDeliveryAddressId(int $deliveryAddressId): OrderEntity
    {
        $this->deliveryAddressId = $deliveryAddressId;

        return $this;
    }

    public function getDebtorPersonId(): int
    {
        return $this->debtorPersonId;
    }

    public function setDebtorPersonId(int $debtorPersonId): OrderEntity
    {
        $this->debtorPersonId = $debtorPersonId;

        return $this;
    }

    public function getDebtorExternalDataId(): int
    {
        return $this->debtorExternalDataId;
    }

    public function setDebtorExternalDataId(int $debtorExternalDataId): OrderEntity
    {
        $this->debtorExternalDataId = $debtorExternalDataId;

        return $this;
    }

    public function getPaymentId(): ?string
    {
        return $this->paymentId;
    }

    public function setPaymentId(?string $paymentId): OrderEntity
    {
        $this->paymentId = $paymentId;

        return $this;
    }

    public function getShippedAt(): ?\DateTime
    {
        return $this->shippedAt;
    }

    public function setShippedAt(\DateTime $shippedAt = null): OrderEntity
    {
        $this->shippedAt = $shippedAt;

        return $this;
    }

    public function getCheckoutSessionId(): ?int
    {
        return $this->checkoutSessionId;
    }

    public function setCheckoutSessionId(?int $checkoutSessionId): OrderEntity
    {
        $this->checkoutSessionId = $checkoutSessionId;

        return $this;
    }

    public function getCreationSource(): string
    {
        return $this->creationSource;
    }

    public function setCreationSource(string $creationSource): OrderEntity
    {
        $this->creationSource = $creationSource;

        return $this;
    }

    public function getStateTransitionEntityClass(): string
    {
        return self::STATE_TRANSITION_ENTITY_CLASS;
    }

    public function setCompanyBillingAddressUuid(?string $uuid): OrderEntity
    {
        $this->companyBillingAddressUuid = $uuid;

        return $this;
    }

    public function getCompanyBillingAddressUuid(): ?string
    {
        return $this->companyBillingAddressUuid;
    }

    public function getWorkflowName(): string
    {
        return $this->workflowName;
    }

    public function setWorkflowName(string $workflowName): OrderEntity
    {
        $this->workflowName = $workflowName;

        return $this;
    }

    public function wasShipped(): bool
    {
        return in_array(
            $this->state,
            [
                self::STATE_SHIPPED,
                self::STATE_PAID_OUT,
                self::STATE_LATE,
            ],
            true
        );
    }

    public function isPreWaiting(): bool
    {
        return $this->state === self::STATE_PRE_WAITING;
    }

    public function isLate(): bool
    {
        return $this->state === self::STATE_LATE;
    }

    public function isDeclined(): bool
    {
        return $this->state === self::STATE_DECLINED;
    }

    public function isComplete(): bool
    {
        return $this->state === self::STATE_COMPLETE;
    }

    public function isCanceled(): bool
    {
        return $this->state === self::STATE_CANCELED;
    }

    public function isPaidOut(): bool
    {
        return $this->state === self::STATE_PAID_OUT;
    }

    public function isWaiting(): bool
    {
        return $this->state === self::STATE_WAITING;
    }

    public function isWorkflowV1(): bool
    {
        return $this->workflowName === self::WORKFLOW_NAME_V1;
    }

    public function isWorkflowV2(): bool
    {
        return $this->workflowName === self::WORKFLOW_NAME_V2;
    }

    public function getDurationExtension(): ?int
    {
        return $this->durationExtension;
    }

    public function setDurationExtension(?int $durationExtension): self
    {
        $this->durationExtension = $durationExtension;

        return $this;
    }

    public function getDebtorSepaMandateUuid(): ?UuidInterface
    {
        return $this->debtorSepaMandateUuid;
    }

    public function setDebtorSepaMandateUuid(?UuidInterface $debtorSepaMandateUuid): OrderEntity
    {
        $this->debtorSepaMandateUuid = $debtorSepaMandateUuid;

        return $this;
    }

    public function getLatestOrderFinancialDetails(): OrderFinancialDetailsEntity
    {
        return $this->latestOrderFinancialDetails;
    }

    public function setLatestOrderFinancialDetails(OrderFinancialDetailsEntity $latestOrderFinancialDetails): self
    {
        $this->latestOrderFinancialDetails = $latestOrderFinancialDetails;

        return $this;
    }

    public function getOrderInvoices(): OrderInvoiceCollection
    {
        return $this->orderInvoices;
    }

    public function setOrderInvoices(OrderInvoiceCollection $orderInvoices): self
    {
        $this->orderInvoices = $orderInvoices;

        return $this;
    }
}
