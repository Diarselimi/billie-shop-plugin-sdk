<?php

declare(strict_types=1);

namespace App\Http\Response\DTO;

use App\DomainModel\ArrayableInterface;

/**
 * @OA\Schema(schema="BankAccount", title="Simple representation of a bank account",
 *     properties={
 *          @OA\Property(property="iban", ref="#/components/schemas/TinyText", description="Virtual IBAN provided by Billie"),
 *          @OA\Property(property="bic", ref="#/components/schemas/TinyText"),
 *     }
 * )
 */
class BankAccountDTO implements ArrayableInterface
{
    private string $iban;

    private string $bic;

    public function __construct(string $iban, string $bic)
    {
        $this->iban = $iban;
        $this->bic = $bic;
    }

    public function toArray(): array
    {
        return [
            'iban' => $this->iban,
            'bic' => $this->bic,
        ];
    }
}
