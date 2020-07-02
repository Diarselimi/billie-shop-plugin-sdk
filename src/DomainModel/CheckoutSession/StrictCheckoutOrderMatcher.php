<?php

declare(strict_types=1);

namespace App\DomainModel\CheckoutSession;

use App\Application\UseCase\CreateOrder\Request\CreateOrderAddressRequest;
use App\DomainModel\Address\AddressEntity;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\CompanyRequestFactory;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

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

    public function matches(CheckoutOrderRequestDTO $request, OrderContainer $orderContainer): bool
    {
        $orderFinancialDetails = $orderContainer->getOrderFinancialDetails();

        $matchAmount = $orderFinancialDetails->getAmountGross()->equals($request->getAmount()->getGross()) &&
            $orderFinancialDetails->getAmountNet()->equals($request->getAmount()->getNet()) &&
            $orderFinancialDetails->getAmountTax()->equals($request->getAmount()->getTax());

        if (!$matchAmount) {
            $this->logInfo('[StrictCheckoutOrderMatcher] Amount mismatch');

            return false;
        }

        $matchDuration = $request->getDuration() === $orderFinancialDetails->getDuration();

        if (!$matchDuration) {
            $this->logInfo('[StrictCheckoutOrderMatcher] Duration mismatch');

            return false;
        }

        $companyNameFromDB = $orderContainer->getDebtorExternalData()->getName();

        $isMatch = $this->strictMatchCompanyAddress($request, $orderContainer->getDebtorExternalDataAddress(), $companyNameFromDB);
        if (!$isMatch && $orderContainer->getOrder()->getCompanyBillingAddressUuid()) {
            $this->logInfo('Debtor company address mismatch, falling back to billing address');

            $isMatch = $this->strictMatchCompanyAddress($request, $orderContainer->getBillingAddress(), $companyNameFromDB);
            if (!$isMatch) {
                $this->logInfo('[StrictCheckoutOrderMatcher] Debtor mismatch');

                return false;
            }
        }

        $isMatch = $this->strictMatchDeliveryAddress(
            $request->getDeliveryAddress(),
            $orderContainer->getDeliveryAddress(),
            $companyNameFromDB
        );

        if (!$isMatch) {
            $this->logInfo('[StrictCheckoutOrderMatcher] Delivery address mismatch');
        }

        return $isMatch;
    }

    private function strictMatchCompanyAddress(CheckoutOrderRequestDTO $request, AddressEntity $addressEntity, string $companyNameFromDB): bool
    {
        $requestCompanyDTO = $this->requestFactory->createCompanyStrictMatchRequestDTO(
            $request->getDebtorCompany(),
            $addressEntity,
            $companyNameFromDB
        );

        return $this->companiesService->strictMatchDebtor($requestCompanyDTO);
    }

    private function strictMatchDeliveryAddress(?CreateOrderAddressRequest $requestDeliveryAddress, AddressEntity $existingDeliveryAddress, string $companyNameFromDB): bool
    {
        if ($requestDeliveryAddress === null) {
            return true;
        }

        $strictMatchDeliveryDTO = $this->requestFactory->createCompanyStrictMatchRequestDTOFromAddress(
            $requestDeliveryAddress,
            $existingDeliveryAddress,
            $companyNameFromDB
        );

        return $this->companiesService->strictMatchDebtor($strictMatchDeliveryDTO);
    }
}
