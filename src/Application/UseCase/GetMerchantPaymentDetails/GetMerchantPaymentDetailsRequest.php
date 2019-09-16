<?php

namespace App\Application\UseCase\GetMerchantPaymentDetails;

use App\Application\UseCase\ValidatedRequestInterface;
use Symfony\Component\Validator\Constraints as Assert;

class GetMerchantPaymentDetailsRequest implements ValidatedRequestInterface
{
    /**
     * @Assert\Type(type="integer")
     * @Assert\NotBlank()
     */
    private $merchantId;

    /**
     * @Assert\Uuid()
     */
    private $transactionUuid;

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function setMerchantId(int $merchantId): GetMerchantPaymentDetailsRequest
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    public function getTransactionUuid(): ?string
    {
        return $this->transactionUuid;
    }

    public function setTransactionUuid(?string $transactionUuid): GetMerchantPaymentDetailsRequest
    {
        $this->transactionUuid = $transactionUuid;

        return $this;
    }
}
