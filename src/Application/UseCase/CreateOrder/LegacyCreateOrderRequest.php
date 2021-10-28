<?php

declare(strict_types=1);

namespace App\Application\UseCase\CreateOrder;

use App\Application\UseCase\CreateOrder\Request\CreateOrderAddressRequest;
use App\Application\UseCase\CreateOrder\Request\CreateOrderDebtorCompanyRequest;
use App\Application\UseCase\CreateOrder\Request\CreateOrderDebtorPersonRequest;
use App\Application\UseCase\ValidatedRequestInterface;
use App\Application\Validator\Constraint as CustomConstrains;
use App\DomainModel\OrderLineItem\OrderLineItemEntity;
use OpenApi\Annotations as OA;
use Ozean12\Money\TaxedMoney\TaxedMoney;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @OA\Schema(schema="LegacyCreateOrderRequest", title="Order Creation Request", required={"amount", "duration", "debtor_company", "debtor_person"},
 *     properties={
 *          @OA\Property(property="amount", ref="#/components/schemas/AmountDTO"),
 *          @OA\Property(property="comment", ref="#/components/schemas/TinyText"),
 *          @OA\Property(property="duration", ref="#/components/schemas/OrderDuration"),
 *          @OA\Property(property="order_id", ref="#/components/schemas/TinyText", description="Order external code", example="DE123456-1"),
 *          @OA\Property(property="delivery_address", ref="#/components/schemas/CreateOrderAddressRequest"),
 *          @OA\Property(property="billing_address", ref="#/components/schemas/CreateOrderAddressRequest"),
 *          @OA\Property(property="debtor_company", ref="#/components/schemas/LegacyCreateOrderDebtorCompanyRequest"),
 *          @OA\Property(property="debtor_person", ref="#/components/schemas/CreateOrderDebtorPersonRequest"),
 *          @OA\Property(
 *              property="line_items",
 *              type="array",
 *              @OA\Items(ref="#/components/schemas/CreateOrderLineItemRequest")
 *          )
 *     }
 * )
 */
class LegacyCreateOrderRequest implements ValidatedRequestInterface, CreateOrderRequestInterface
{
    /**
     * @Assert\NotBlank()
     * @Assert\Valid()
     * @var TaxedMoney
     */
    private $amount;

    /**
     * @Assert\Length(max=255)
     */
    private $comment;

    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="integer")
     * @CustomConstrains\OrderDuration()
     */
    private $duration;

    /**
     * @Assert\NotBlank(allowNull=true, message="This value should be null or non-blank string.")
     * @CustomConstrains\OrderExternalCode()
     * @Assert\Type(type="string")
     * @Assert\Length(max=255)
     */
    private $externalCode;

    /**
     * @Assert\Valid()
     */
    private $deliveryAddress;

    /**
     * @Assert\Valid()
     */
    private $debtorCompany;

    /**
     * @Assert\Valid()
     */
    private $debtorPerson;

    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="integer")
     */
    private $merchantId;

    private $checkoutSessionId;

    private $creationSource;

    private $workflowName;

    /**
     * @Assert\Valid()
     */
    private $billingAddress;

    /**
     * @Assert\Valid()
     * @Assert\NotBlank(groups={"AuthorizeOrder"})
     */
    private $lineItems;

    /**
     * @Assert\DateTime(format="Y-m-d\TH:i:sO", groups={"AuthorizeOrder"})
     */
    private ?string $expiration = null;

    public function getAmount(): TaxedMoney
    {
        return $this->amount;
    }

    public function setAmount(TaxedMoney $amount): LegacyCreateOrderRequest
    {
        $this->amount = $amount;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): LegacyCreateOrderRequest
    {
        $this->comment = $comment;

        return $this;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): LegacyCreateOrderRequest
    {
        $this->duration = $duration;

        return $this;
    }

    public function getExternalCode(): ?string
    {
        return $this->externalCode;
    }

    public function setExternalCode($externalCode): LegacyCreateOrderRequest
    {
        $this->externalCode = $externalCode;

        return $this;
    }

    public function getDeliveryAddress(): ?CreateOrderAddressRequest
    {
        return $this->deliveryAddress;
    }

    public function setDeliveryAddress(?CreateOrderAddressRequest $deliveryAddress): LegacyCreateOrderRequest
    {
        $this->deliveryAddress = $deliveryAddress;

        return $this;
    }

    public function getDebtorPerson(): CreateOrderDebtorPersonRequest
    {
        return $this->debtorPerson;
    }

    public function setDebtorPerson(CreateOrderDebtorPersonRequest $debtorPerson): LegacyCreateOrderRequest
    {
        $this->debtorPerson = $debtorPerson;

        return $this;
    }

    public function getDebtor(): CreateOrderDebtorCompanyRequest
    {
        return $this->debtorCompany;
    }

    public function setDebtor(CreateOrderDebtorCompanyRequest $debtorCompany): LegacyCreateOrderRequest
    {
        $this->debtorCompany = $debtorCompany;

        return $this;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function setMerchantId(int $merchantId): LegacyCreateOrderRequest
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    public function getCheckoutSessionId(): ?int
    {
        return $this->checkoutSessionId;
    }

    public function setCheckoutSessionId(?int $checkoutSessionId): LegacyCreateOrderRequest
    {
        $this->checkoutSessionId = $checkoutSessionId;

        return $this;
    }

    public function getCreationSource(): string
    {
        return $this->creationSource;
    }

    public function setCreationSource(string $creationSource): LegacyCreateOrderRequest
    {
        $this->creationSource = $creationSource;

        return $this;
    }

    public function getWorkflowName(): string
    {
        return $this->workflowName;
    }

    public function setWorkflowName(string $workflowName): LegacyCreateOrderRequest
    {
        $this->workflowName = $workflowName;

        return $this;
    }

    public function getBillingAddress(): ?CreateOrderAddressRequest
    {
        return $this->billingAddress;
    }

    public function setBillingAddress(?CreateOrderAddressRequest $billingAddress): LegacyCreateOrderRequest
    {
        $this->billingAddress = $billingAddress;

        return $this;
    }

    /**
     * @return OrderLineItemEntity[]
     */
    public function getLineItems(): array
    {
        return $this->lineItems;
    }

    public function setLineItems(array $lineItems): LegacyCreateOrderRequest
    {
        $this->lineItems = $lineItems;

        return $this;
    }

    public function setExpiration(?string $expiration): LegacyCreateOrderRequest
    {
        $this->expiration = $expiration;

        return $this;
    }

    public function getExpiration(): ?string
    {
        return $this->expiration;
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
            'address_city' => $this->getDebtor()->getAddressCity(),
            'address_postal_code' => $this->getDebtor()->getAddressPostalCode(),
            'address_street' => $this->getDebtor()->getAddressStreet(),
            'address_house_number' => $this->getDebtor()->getAddressHouseNumber(),
            'address_house_country' => $this->getDebtor()->getAddressCountry(),
        ];
    }
}
