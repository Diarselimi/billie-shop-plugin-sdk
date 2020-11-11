<?php

declare(strict_types=1);

namespace App\Application\UseCase\CreateOrder;

use App\Application\UseCase\CreateOrder\Request\CreateOrderDebtorCompanyRequest;
use App\Application\UseCase\CreateOrder\Request\CreateOrderDebtorPersonRequest;
use App\Application\UseCase\CreateOrder\Request\CreateOrderAddressRequest;
use App\Application\UseCase\ValidatedRequestInterface;
use App\Application\Validator\Constraint as CustomConstrains;
use Ozean12\Money\TaxedMoney\TaxedMoney;
use App\DomainModel\ArrayableInterface;
use App\DomainModel\OrderLineItem\OrderLineItemEntity;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @OA\Schema(schema="CreateOrderRequest", title="Order Creation Request", required={"amount", "duration", "debtor_company", "debtor_person"},
 *     properties={
 *          @OA\Property(property="amount", ref="#/components/schemas/AmountDTO"),
 *          @OA\Property(property="comment", ref="#/components/schemas/TinyText", nullable=true),
 *          @OA\Property(property="duration", ref="#/components/schemas/OrderDuration"),
 *          @OA\Property(property="order_id", ref="#/components/schemas/TinyText", description="Order external code", example="DE123456-1"),
 *          @OA\Property(property="delivery_address", ref="#/components/schemas/CreateOrderAddressRequest", nullable=true),
 *          @OA\Property(property="billing_address", ref="#/components/schemas/CreateOrderAddressRequest", nullable=true),
 *          @OA\Property(property="debtor_company", ref="#/components/schemas/CreateOrderDebtorCompanyRequest"),
 *          @OA\Property(property="debtor_person", ref="#/components/schemas/CreateOrderDebtorPersonRequest"),
 *          @OA\Property(
 *              property="line_items",
 *              type="array",
 *              nullable=true,
 *              @OA\Items(ref="#/components/schemas/CreateOrderLineItemRequest")
 *          )
 *     }
 * )
 */
class CreateOrderRequest implements ValidatedRequestInterface, ArrayableInterface
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

    public function getAmount(): TaxedMoney
    {
        return $this->amount;
    }

    public function setAmount(TaxedMoney $amount): CreateOrderRequest
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

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): CreateOrderRequest
    {
        $this->duration = $duration;

        return $this;
    }

    public function getExternalCode(): ?string
    {
        return $this->externalCode;
    }

    public function setExternalCode($externalCode): CreateOrderRequest
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

    public function getDebtorPerson(): CreateOrderDebtorPersonRequest
    {
        return $this->debtorPerson;
    }

    public function setDebtorPerson(CreateOrderDebtorPersonRequest $debtorPerson): CreateOrderRequest
    {
        $this->debtorPerson = $debtorPerson;

        return $this;
    }

    public function getDebtorCompany(): CreateOrderDebtorCompanyRequest
    {
        return $this->debtorCompany;
    }

    public function setDebtorCompany(CreateOrderDebtorCompanyRequest $debtorCompany): CreateOrderRequest
    {
        $this->debtorCompany = $debtorCompany;

        return $this;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function setMerchantId(int $merchantId): CreateOrderRequest
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    public function getCheckoutSessionId(): ?int
    {
        return $this->checkoutSessionId;
    }

    public function setCheckoutSessionId(?int $checkoutSessionId): CreateOrderRequest
    {
        $this->checkoutSessionId = $checkoutSessionId;

        return $this;
    }

    public function getCreationSource(): string
    {
        return $this->creationSource;
    }

    public function setCreationSource(string $creationSource): CreateOrderRequest
    {
        $this->creationSource = $creationSource;

        return $this;
    }

    public function getWorkflowName(): string
    {
        return $this->workflowName;
    }

    public function setWorkflowName(string $workflowName): CreateOrderRequest
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

    /**
     * @return OrderLineItemEntity[]
     */
    public function getLineItems(): array
    {
        return $this->lineItems;
    }

    public function setLineItems(array $lineItems): CreateOrderRequest
    {
        $this->lineItems = $lineItems;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'company_name' => $this->getDebtorCompany()->getName(),
            'tax_id' => $this->getDebtorCompany()->getTaxId(),
            'tax_number' => $this->getDebtorCompany()->getTaxNumber(),
            'registration_court' => $this->getDebtorCompany()->getRegistrationCourt(),
            'registration_number' => $this->getDebtorCompany()->getRegistrationNumber(),
            'legal_form' => $this->getDebtorCompany()->getLegalForm(),
            'address_city' => $this->getDebtorCompany()->getAddressCity(),
            'address_postal_code' => $this->getDebtorCompany()->getAddressPostalCode(),
            'address_street' => $this->getDebtorCompany()->getAddressStreet(),
            'address_house_number' => $this->getDebtorCompany()->getAddressHouseNumber(),
            'address_house_country' => $this->getDebtorCompany()->getAddressCountry(),
        ];
    }
}
