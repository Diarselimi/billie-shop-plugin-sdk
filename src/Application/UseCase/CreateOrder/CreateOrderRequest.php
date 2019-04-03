<?php

namespace App\Application\UseCase\CreateOrder;

use App\Application\UseCase\CreateOrder\Request\CreateOrderAmountRequest;
use App\Application\UseCase\CreateOrder\Request\CreateOrderDebtorCompanyRequest;
use App\Application\UseCase\CreateOrder\Request\CreateOrderDebtorPersonRequest;
use App\Application\UseCase\CreateOrder\Request\CreateOrderDeliveryAddressRequest;
use App\Application\UseCase\ValidatedRequestInterface;
use Symfony\Component\Validator\Constraints as Assert;
use App\Application\Validator\Constraint as CreateOrderUseCaseConstraints;

class CreateOrderRequest implements ValidatedRequestInterface
{
    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="numeric")
     */
    private $merchantId;

    /**
     * @Assert\Valid()
     */
    private $amount;

    /**
     * @Assert\Length(max=255)
     */
    private $comment;

    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="int")
     * @CreateOrderUseCaseConstraints\OrderAmountConstraint
     */
    private $duration;

    /**
     * @CreateOrderUseCaseConstraints\OrderExternalCode()
     * @Assert\NotBlank()
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

    public function getMerchantId()
    {
        return $this->merchantId;
    }

    public function setMerchantId($merchantId): CreateOrderRequest
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    public function getAmount(): CreateOrderAmountRequest
    {
        return $this->amount;
    }

    public function setAmount(CreateOrderAmountRequest $amount): CreateOrderRequest
    {
        $this->amount = $amount;

        return $this;
    }

    public function getComment()
    {
        return $this->comment;
    }

    public function setComment($comment): CreateOrderRequest
    {
        $this->comment = $comment;

        return $this;
    }

    public function getDuration()
    {
        return $this->duration;
    }

    public function setDuration($duration): CreateOrderRequest
    {
        $this->duration = $duration;

        return $this;
    }

    public function getExternalCode()
    {
        return $this->externalCode;
    }

    public function setExternalCode($externalCode): CreateOrderRequest
    {
        $this->externalCode = $externalCode;

        return $this;
    }

    public function getDeliveryAddress(): CreateOrderDeliveryAddressRequest
    {
        return $this->deliveryAddress;
    }

    public function setDeliveryAddress(CreateOrderDeliveryAddressRequest $deliveryAddress): CreateOrderRequest
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
}
