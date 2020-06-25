<?php

declare(strict_types=1);

namespace App\DomainModel\CheckoutSession;

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

        $requestCompanyDTO = $this->requestFactory->createCompanyStrictMatchRequestDTO(
            $request->getDebtorCompany(),
            $orderContainer->getDebtorExternalDataAddress(),
            $orderContainer->getDebtorExternalData()->getName()
        );

        $matchDebtor = $this->companiesService->strictMatchDebtor($requestCompanyDTO);
        if (!$matchDebtor && $orderContainer->getOrder()->getCompanyBillingAddressUuid()) {
            $this->logInfo('Debtor company address mismatch, falling back to billing address');

            $requestBillingDTO = $this->requestFactory->createCompanyStrictMatchRequestDTO(
                $request->getDebtorCompany(),
                $orderContainer->getBillingAddress(),
                $orderContainer->getDebtorExternalData()->getName()
            );

            $matchDebtor = $this->companiesService->strictMatchDebtor($requestBillingDTO);
            if (!$matchDebtor) {
                $this->logInfo('[StrictCheckoutOrderMatcher] Debtor mismatch');

                return false;
            }
        }

        return true;
    }
}
