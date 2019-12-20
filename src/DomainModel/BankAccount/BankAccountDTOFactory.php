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
        IbanDTOFactory $ibanDTOFactory,
        BicLookupServiceInterface $bicLookupService,
        UuidGeneratorInterface $uuidGenerator
    ) {
        $this->ibanDTOFactory = $ibanDTOFactory;
        $this->bicLookupService = $bicLookupService;
        $this->uuidGenerator = $uuidGenerator;
    }

    public function create(string $name, string $iban, string $paymentUuid): BankAccountDTO
    {
        $iban = $this->ibanDTOFactory->createFromString($iban);
        $bic = $this->bicLookupService->lookup($iban);

        return (new BankAccountDTO())
            ->setUuid($this->uuidGenerator->uuid4())
            ->setIban($iban)
            ->setBic($bic)
            ->setName($name)
            ->setPaymentUuid($paymentUuid);
    }
}
