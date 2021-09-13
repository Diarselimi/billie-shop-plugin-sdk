<?php

declare(strict_types=1);

namespace App\Application\UseCase\CheckoutProvideIban;

use App\Application\UseCase\ValidatedRequestInterface;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @OA\Schema(
 *      schema="CheckoutProvideIbanRequest",
 *      x={"groups": {"private"}},
 *      type="object",
 *      properties={
 *          @OA\Property(property="iban", ref="#/components/schemas/IBAN"),
 *          @OA\Property(property="bank_account_owner", ref="#/components/schemas/TinyText"),
 *      },
 *      required={"iban"}
 * )
 */
final class CheckoutProvideIbanRequest implements ValidatedRequestInterface
{
    private string $sessionUuid;

    /**
     * @Assert\NotBlank()
     * @Assert\Iban()
     * @Assert\Regex(pattern="/^DE.*$/", message="Only German IBANs are allowed")
     */
    private ?string $iban;

    /**
     * @Assert\NotBlank()
     */
    private ?string $bankAccountOwner;

    public function __construct(string $sessionUuid, ?string $iban, ?string $bankAccountOwner)
    {
        $this->sessionUuid = $sessionUuid;
        $this->iban = $iban;
        $this->bankAccountOwner = $bankAccountOwner;
    }

    public function getSessionUuid(): string
    {
        return $this->sessionUuid;
    }

    public function getIban(): ?string
    {
        return $this->iban;
    }

    public function getBankAccountOwner(): ?string
    {
        return $this->bankAccountOwner;
    }
}
