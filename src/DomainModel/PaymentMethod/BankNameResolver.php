<?php

declare(strict_types=1);

namespace App\DomainModel\PaymentMethod;

use App\DomainModel\BankAccount\BankAccountServiceException;
use App\DomainModel\BankAccount\BankAccountServiceInterface;
use App\DomainModel\BankAccount\BankNotFoundException;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Ozean12\Support\ValueObject\BankAccount;

class BankNameResolver implements LoggingInterface
{
    use LoggingTrait;

    private BankAccountServiceInterface $bankAccountService;

    private array $cache = [];

    public function __construct(BankAccountServiceInterface $bankAccountService)
    {
        $this->bankAccountService = $bankAccountService;
    }

    public function resolve(BankAccount $bankAccount): BankAccount
    {
        if ($bankAccount->hasBankName()) {
            return $bankAccount;
        }

        try {
            $bankName = $this->cache[$bankAccount->getBic()] = $this->cache[$bankAccount->getBic()]
                ?? $this->bankAccountService->getBankByBic($bankAccount->getBic())->getName();
        } catch (BankAccountServiceException | BankNotFoundException $exception) {
            $this->logDebug(
                'Banco getBankByBic failed: ' . $exception->getMessage() . '. BIC was ' . $bankAccount->getBic()
            );
            $bankName = null;
        }

        return new BankAccount(
            $bankAccount->getIban(),
            $bankAccount->getBic(),
            $bankName,
            $bankAccount->getAccountHolder()
        );
    }
}
