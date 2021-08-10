<?php

declare(strict_types=1);

namespace App\DomainModel\PaymentMethod;

use App\DomainModel\BankAccount\BankAccountServiceException;
use App\DomainModel\BankAccount\BankAccountServiceInterface;
use App\DomainModel\BankAccount\BankNotFoundException;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Ozean12\InvoiceButler\Client\DomainModel\PaymentMethod\PaymentMethod as ClientPaymentMethod;
use Ozean12\InvoiceButler\Client\DomainModel\PaymentMethod\PaymentMethodCollection as ClientCollection;
use Ozean12\Support\ValueObject\BankAccount;

class BankNameDecorator implements LoggingInterface
{
    use LoggingTrait;

    private BankAccountServiceInterface $bankAccountService;

    public function __construct(BankAccountServiceInterface $bankAccountService)
    {
        $this->bankAccountService = $bankAccountService;
    }

    public function addBankName(ClientCollection $clientCollection): PaymentMethodCollection
    {
        $paymentMethods = [];
        foreach ($clientCollection as $clientPaymentMethod) {
            /** @var ClientPaymentMethod $clientPaymentMethod */
            if ($clientPaymentMethod->isBankTransfer()) {
                $bankAccount = $clientPaymentMethod->getBankAccount();

                try {
                    $bankName = $bankAccount->getBankName() ?: $this->bankAccountService->getBankByBic(
                        $bankAccount->getBic()
                    )->getName();
                } catch (BankAccountServiceException | BankNotFoundException $exception) {
                    $this->logDebug(
                        'Banco getBankByBic failed: ' . $exception->getMessage()
                        . '. BIC was ' . $bankAccount->getBic()
                    );
                    $bankName = null;
                }
                $paymentMethods[] = new PaymentMethod(
                    PaymentMethod::TYPE_BANK_TRANSFER,
                    new BankAccount($bankAccount->getIban(), $bankAccount->getBic(), $bankName, null)
                );
            }
        }

        return new PaymentMethodCollection($paymentMethods);
    }
}
