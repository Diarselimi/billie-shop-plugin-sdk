<?php

declare(strict_types=1);

namespace App\Application\UseCase\CreateOrder;

use App\Application\UseCase\CreateOrder\Request\CreateOrderAddressRequest;
use App\Application\UseCase\CreateOrder\Request\CreateOrderDebtorCompanyRequest;
use App\Application\UseCase\CreateOrder\Request\CreateOrderDebtorPersonRequest;
use App\Application\UseCase\ValidatedRequestInterface;
use App\Application\Validator\Constraint as CustomConstrains;
use OpenApi\Annotations as OA;
use Ozean12\Money\TaxedMoney\TaxedMoney;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @OA\Schema(schema="CreateOrderRequest", title="Order Creation Request", required={"amount", "duration", "debtor_company", "debtor_person", "line_items"},
 *     properties={
 *          @OA\Property(property="amount", ref="#/components/schemas/AmountDTO"),
 *          @OA\Property(property="comment", ref="#/components/schemas/TinyText", nullable=true),
 *          @OA\Property(property="duration", ref="#/components/schemas/OrderDuration"),
 *          @OA\Property(property="external_code", ref="#/components/schemas/TinyText", description="Order external code", example="DE123456-1"),
 *          @OA\Property(property="delivery_address", ref="#/components/schemas/Address", nullable=true),
 *          @OA\Property(property="debtor", ref="#/components/schemas/CreateOrderDebtorCompanyRequest"),
 *          @OA\Property(property="debtor_person", ref="#/components/schemas/CreateOrderDebtorPersonRequest"),
 *          @OA\Property(
 *              property="line_items",
 *              type="array",
 *              @OA\Items(ref="#/components/schemas/CreateOrderLineItemRequest")
 *          )
 *     }
 * )
 */
class CreateOrderRequest implements ValidatedRequestInterface, CreateOrderRequestInterface
{
    /**
     * @Assert\NotBlank()
     * @Assert\Valid()
     */
    private ?TaxedMoney $amount;

    /**
     * @Assert\Length(max=255)
     */
    private ?string $comment;

    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="integer")
     * @CustomConstrains\OrderDuration()
     */
    private ?int $duration;

    /**
     * @Assert\NotBlank(allowNull=true, message="This value should be null or non-blank string.")
     * @CustomConstrains\OrderExternalCode()
     * @Assert\Length(max=255)
     */
    private ?string $externalCode;

    /**
     * @Assert\Valid()
     */
    private ?CreateOrderAddressRequest $deliveryAddress;

    /**
     * @Assert\Valid()
     */
    private ?CreateOrderDebtorCompanyRequest $debtor;

    /**
     * @Assert\Valid()
     */
    private ?CreateOrderDebtorPersonRequest $debtorPerson;

    /**
     * @Assert\NotBlank()
     */
    private ?int $merchantId;

    private ?string $checkoutSessionId;

    private ?string $creationSource;

    private ?string $workflowName;

    /**
     * @Assert\Valid()
     */
    private ?CreateOrderAddressRequest $billingAddress = null;

    /**
     * @Assert\Valid()
     */
    private ?array $lineItems;

    public function getAmount(): ?TaxedMoney
    {
        return $this->amount;
    }

    public function setAmount(?TaxedMoney $amount): CreateOrderRequest
    {
        $this->amount = $amount;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): CreateOrderRequest
    {
        $this->comment = $comment;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): CreateOrderRequest
    {
        $this->duration = $duration;

        return $this;
    }

    public function getExternalCode(): ?string
    {
        return $this->externalCode;
    }

    public function setExternalCode(?string $externalCode): CreateOrderRequest
    {
        $this->externalCode = $externalCode;

        return $this;
    }

    public function getDeliveryAddress(): ?CreateOrderAddressRequest
    {
        return $this->deliveryAddress;
    }

    public function setDeliveryAddress(?CreateOrderAddressRequest $deliveryAddress): CreateOrderRequest
    {
        $this->deliveryAddress = $deliveryAddress;

        return $this;
    }

    public function getDebtor(): ?CreateOrderDebtorCompanyRequest
    {
        return $this->debtor;
    }

    public function setDebtor(?CreateOrderDebtorCompanyRequest $debtor): CreateOrderRequest
    {
        $this->debtor = $debtor;

        return $this;
    }

    public function getDebtorPerson(): ?CreateOrderDebtorPersonRequest
    {
        return $this->debtorPerson;
    }

    public function setDebtorPerson(?CreateOrderDebtorPersonRequest $debtorPerson): CreateOrderRequest
    {
        $this->debtorPerson = $debtorPerson;

        return $this;
    }

    public function getMerchantId(): ?int
    {
        return $this->merchantId;
    }

    public function setMerchantId(?int $merchantId): CreateOrderRequest
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    public function getCheckoutSessionId(): ?string
    {
        return $this->checkoutSessionId;
    }

    public function setCheckoutSessionId(?string $checkoutSessionId): CreateOrderRequest
    {
        $this->checkoutSessionId = $checkoutSessionId;

        return $this;
    }

    public function getCreationSource(): ?string
    {
        return $this->creationSource;
    }

    public function setCreationSource(?string $creationSource): CreateOrderRequest
    {
        $this->creationSource = $creationSource;

        return $this;
    }

    public function getWorkflowName(): ?string
    {
        return $this->workflowName;
    }

    public function setWorkflowName(?string $workflowName): CreateOrderRequest
    {
        $this->workflowName = $workflowName;

        return $this;
    }

    public function getBillingAddress(): ?CreateOrderAddressRequest
    {
        return $this->billingAddress;
    }

    public function setBillingAddress(?CreateOrderAddressRequest $billingAddress): CreateOrderRequest
    {
        $this->billingAddress = $billingAddress;

        return $this;
    }

    public function getLineItems(): ?array
    {
        return $this->lineItems;
    }

    public function setLineItems(?array $lineItems): CreateOrderRequest
    {
        $this->lineItems = $lineItems;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'company_name' => $this->getDebtor()->getName(),
            'tax_id' => $this->getDebtor()->getTaxId(),
            'tax_number' => $this->getDebtor()->getTaxNumber(),
            'registration_court' => $this->getDebtor()->getRegistrationCourt(),
            'registration_number' => $this->getDebtor()->getRegistrationNumber(),
            'legal_form' => $this->getDebtor()->getLegalForm(),
            'address' => $this->getDebtor()->getAddress()->toArray(),
        ];
    }
}
