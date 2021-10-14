<?php

declare(strict_types=1);

namespace App\DomainModel\PaymentMethod;

use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Ozean12\InvoiceButler\Client\DomainModel\PaymentMethod\PaymentMethod as ClientPaymentMethod;
use Ozean12\InvoiceButler\Client\DomainModel\PaymentMethod\PaymentMethodCollection as ClientCollection;
use Ozean12\Support\ValueObject\BankAccount;

class BankNameDecorator implements LoggingInterface
{
    use LoggingTrait;

    private BankNameResolver $bankNameResolver;

    public function __construct(BankNameResolver $bankNameResolver)
    {
        $this->bankNameResolver = $bankNameResolver;
    }

    public function addBankName(ClientCollection $clientCollection): PaymentMethodCollection
    {
        $paymentMethods = [];
        foreach ($clientCollection as $clientPaymentMethod) {
            /** @var ClientPaymentMethod $clientPaymentMethod */
            $bankAccount = $clientPaymentMethod->getBankAccount();
            $bankName = $this->bankNameResolver->resolve($bankAccount)->getBankName();

            $paymentMethods[] = new PaymentMethod(
                $clientPaymentMethod->getType(),
                new BankAccount(
                    $bankAccount->getIban(),
                    $bankAccount->getBic(),
                    $bankName,
                    $bankAccount->getAccountHolder()
                ),
                $clientPaymentMethod->getSepaMandate(),
                $clientPaymentMethod->getSepaMandateExecutionDate()
            );
        }

        return new PaymentMethodCollection($paymentMethods);
    }
}
