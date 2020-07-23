<?php

declare(strict_types=1);

namespace App\DomainModel\CheckoutSession;

use App\Application\UseCase\CreateOrder\Request\CreateOrderAddressRequest;
use App\DomainModel\Address\AddressEntity;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\CompanyRequestFactory;
use App\DomainModel\DebtorCompany\DebtorCompanyRequest;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Ozean12\Money\TaxedMoney\TaxedMoney;

class StrictCheckoutOrderMatcher implements CheckoutOrderMatcherInterface, LoggingInterface
{
    use LoggingTrait;

    private $companiesService;

    private $requestFactory;

    public function __construct(CompaniesServiceInterface $companiesService, CompanyRequestFactory $requestFactory)
    {
        $this->companiesService = $companiesService;
        $this->requestFactory = $requestFactory;
    }

    public function matches(
        CheckoutOrderRequestDTO $request,
        OrderContainer $orderContainer
    ): CheckoutOrderMatcherViolationList {
        $result = new CheckoutOrderMatcherViolationList();
        $financialDetails = $orderContainer->getOrderFinancialDetails();

        $matchAmount = (new TaxedMoney(
            $financialDetails->getAmountGross(),
            $financialDetails->getAmountNet(),
            $financialDetails->getAmountTax()
        ))->equals($request->getAmount());

        if (!$matchAmount) {
            $this->logInfo('[StrictCheckoutOrderMatcher] Amount mismatch');
            $result->addMismatch(
                'amount',
                [
                    'gross' => $request->getAmount()->getGross()->getMoneyValue(),
                    'net' => $request->getAmount()->getNet()->getMoneyValue(),
                    'tax' => $request->getAmount()->getTax()->getMoneyValue(),
                ]
            );
        }

        $matchDuration = $request->getDuration() === $financialDetails->getDuration();

        if (!$matchDuration) {
            $this->logInfo('[StrictCheckoutOrderMatcher] Duration mismatch');
            $result->addMismatch('duration', $request->getDuration());
        }

        if ($request->getDeliveryAddress() !== null && $this->isDeliveryAddressMismatch($request, $orderContainer)) {
            $result->addMismatch('delivery_address', $request->getDeliveryAddress()->toArray());
        }

        if ($this->isCompanyAddressMismatch($request, $orderContainer)) {
            $hasBillingAddress = ($orderContainer->getOrder()->getCompanyBillingAddressUuid() !== null);

            if ($hasBillingAddress) {
                $this->logInfo(
                    '[StrictCheckoutOrderMatcher] Debtor company address mismatch, falling back to billing address'
                );
                if ($this->isCompanyBillingAddressMismatch($request, $orderContainer)) {
                    $result->addMismatch('debtor_company', $request->getDebtorCompany()->toArray());
                } else {
                    $this->logInfo('[StrictCheckoutOrderMatcher] Matching billing address found.');
                }
            } else {
                $result->addMismatch('debtor_company', $request->getDebtorCompany()->toArray());
            }
        }

        return $result;
    }

    private function isCompanyAddressMismatch(
        CheckoutOrderRequestDTO $request,
        OrderContainer $orderContainer
    ): bool {
        $companyName = $orderContainer->getDebtorCompany()->getName();
        $companyAddress = $orderContainer->getDebtorCompany()->getDebtorAddress();

        return !$this->checkCompanyMatch(
            $request->getDebtorCompany(),
            $companyAddress,
            $companyName
        );
    }

    private function isCompanyBillingAddressMismatch(
        CheckoutOrderRequestDTO $request,
        OrderContainer $orderContainer
    ): bool {
        $companyName = $orderContainer->getDebtorCompany()->getName();

        $isMatch = $this->checkCompanyMatch(
            $request->getDebtorCompany(),
            $orderContainer->getBillingAddress(),
            $companyName
        );
        if (!$isMatch) {
            $this->logInfo('[StrictCheckoutOrderMatcher] Debtor billing address mismatch');

            return true;
        }

        return false;
    }

    private function isDeliveryAddressMismatch(
        CheckoutOrderRequestDTO $request,
        OrderContainer $orderContainer
    ): bool {
        $isMatch = $this->checkAddressMatch(
            $request->getDeliveryAddress(),
            $orderContainer->getDeliveryAddress()
        );
        if (!$isMatch) {
            $this->logInfo('[StrictCheckoutOrderMatcher] Debtor delivery address mismatch');

            return true;
        }

        return false;
    }

    private function checkCompanyMatch(
        DebtorCompanyRequest $requestCompany,
        AddressEntity $addressToCompare,
        string $nameToCompare
    ): bool {
        $requestCompanyDTO = $this->requestFactory->createCompanyStrictMatchRequestDTO(
            $requestCompany,
            $addressToCompare,
            $nameToCompare
        );

        return $this->companiesService->strictMatchDebtor($requestCompanyDTO);
    }

    private function checkAddressMatch(
        CreateOrderAddressRequest $requestAddress,
        AddressEntity $addressToCompare
    ): bool {
        $requestCompanyDTO = $this->requestFactory->createCompanyStrictMatchRequestDTOFromAddress(
            $requestAddress,
            $addressToCompare
        );

        return $this->companiesService->strictMatchDebtor($requestCompanyDTO);
    }
}
