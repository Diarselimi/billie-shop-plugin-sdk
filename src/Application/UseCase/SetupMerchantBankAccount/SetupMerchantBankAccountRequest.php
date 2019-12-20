<?php

declare(strict_types=1);

namespace App\Application\UseCase\SetupMerchantBankAccount;

use App\Application\UseCase\ValidatedRequestInterface;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @OA\Schema(
 *      schema="SetupMerchantBankAccountRequest",
 *      x={"groups": {"private"}},
 *      type="object",
 *      properties={
 *          @OA\Property(property="iban", ref="#/components/schemas/IBAN"),
 *          @OA\Property(property="tc_accepted", type="boolean"),
 *      },
 *      required={"iban", "tc_accepted"}
 * )
 */
class SetupMerchantBankAccountRequest implements ValidatedRequestInterface
{
    private $merchantId;

    /**
     * @Assert\NotBlank()
     * @Assert\Iban()
     */
    private $iban;

    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="boolean")
     * @Assert\EqualTo(value=true)
     */
    private $tcAccepted;

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function getIban(): string
    {
        return $this->iban;
    }

    public function isTcAccepted(): bool
    {
        return $this->tcAccepted;
    }

    public function setMerchantId(int $merchantId): SetupMerchantBankAccountRequest
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    public function setIban($iban): SetupMerchantBankAccountRequest
    {
        $this->iban = $iban;

        return $this;
    }

    public function setTcAccepted($tcAccepted): SetupMerchantBankAccountRequest
    {
        $this->tcAccepted = $tcAccepted;

        return $this;
    }
}
