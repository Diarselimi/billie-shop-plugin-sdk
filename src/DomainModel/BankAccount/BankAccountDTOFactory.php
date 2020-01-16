<?php

declare(strict_types=1);

namespace App\DomainModel\BankAccount;

use App\Helper\Uuid\UuidGeneratorInterface;

class BankAccountDTOFactory
{
    private $ibanDTOFactory;

    private $bicLookupService;

    private $uuidGenerator;

    public function __construct(
        UuidGeneratorInterface $uuidGenerator
    ) {
        $this->uuidGenerator = $uuidGenerator;
    }

    public function create(string $name, IbanDTO $iban, string $bic, string $paymentUuid): BankAccountDTO
    {
        return (new BankAccountDTO())
            ->setUuid($this->uuidGenerator->uuid4())
            ->setIban($iban)
            ->setBic($bic)
            ->setName($name)
            ->setPaymentUuid($paymentUuid);
    }
}
