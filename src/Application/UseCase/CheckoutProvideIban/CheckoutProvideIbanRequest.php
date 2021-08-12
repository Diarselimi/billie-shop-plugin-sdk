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
     */
    private ?string $iban;

    public function __construct(string $sessionUuid, ?string $iban)
    {
        $this->sessionUuid = $sessionUuid;
        $this->iban = $iban;
    }

    public function getSessionUuid(): string
    {
        return $this->sessionUuid;
    }

    public function getIban(): ?string
    {
        return $this->iban;
    }
}
