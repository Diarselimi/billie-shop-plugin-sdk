<?php

namespace App\Application\UseCase\GetOrder;

use App\Application\PaellaCoreCriticalException;
use App\DomainModel\Address\AddressRepositoryInterface;
use App\DomainModel\Alfred\AlfredInterface;
use App\DomainModel\Borscht\BorschtInterface;
use App\DomainModel\Company\CompanyRepositoryInterface;
use App\DomainModel\DebtorExternalData\DebtorExternalDataRepositoryInterface;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use Symfony\Component\HttpFoundation\Response;

class GetOrderUseCase
{
    private $orderRepository;
    private $companyRepository;
    private $addressRepository;
    private $debtorExternalDataRepository;
    private $alfred;
    private $borscht;
    private $orderStateManager;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        CompanyRepositoryInterface $companyRepository,
        AddressRepositoryInterface $addressRepository,
        DebtorExternalDataRepositoryInterface $debtorExternalDataRepository,
        AlfredInterface $alfred,
        BorschtInterface $borscht,
        OrderStateManager $orderStateManager
    ) {
        $this->orderRepository = $orderRepository;
        $this->companyRepository = $companyRepository;
        $this->addressRepository = $addressRepository;
        $this->debtorExternalDataRepository = $debtorExternalDataRepository;
        $this->alfred = $alfred;
        $this->borscht = $borscht;
        $this->orderStateManager = $orderStateManager;
    }

    public function execute(GetOrderRequest $request): GetOrderResponse
    {
        $externalCode = $request->getExternalCode();
        $customerId = $request->getCustomerId();
        $order = $this->orderRepository->getOneByExternalCode($externalCode, $customerId);

        if (!$order) {
            throw new PaellaCoreCriticalException(
                "Order #$externalCode not found",
                PaellaCoreCriticalException::CODE_NOT_FOUND,
                Response::HTTP_NOT_FOUND
            );
        }

        $debtorData = $this->debtorExternalDataRepository->getOneByIdRaw($order->getDebtorExternalDataId());
        $debtorAddress = $this->addressRepository->getOneByIdRaw($debtorData['address_id']);

        $response = (new GetOrderResponse())
            ->setExternalCode($order->getExternalCode())
            ->setState($order->getState())
            ->setOriginalAmount($order->getAmountGross())
            ->setDebtorExternalDataAddressCountry($debtorAddress['country'])
            ->setDebtorExternalDataIndustrySector($debtorData['industry_sector'])
        ;

        if ($order->getCompanyId()) {
            $this->addCompanyToOrder($order, $response);
        }

        if ($this->orderStateManager->wasShipped($order)) {
            $this->addInvoiceToOrder($order, $response);
        }

        if ($this->orderStateManager->isDeclined($order)) {
            $response->setReasons(['risk_policy']);
        }

        return $response;
    }

    private function addCompanyToOrder(OrderEntity $order, GetOrderResponse $response)
    {
        $company = $this->companyRepository->getOneById($order->getCompanyId());
        $debtor = $this->alfred->getDebtor($company->getDebtorId());
        $debtorPaymentDetails = $this->borscht->getDebtorPaymentDetails($debtor->getPaymentId());

        $response
            ->setCompanyName($debtor->getName())
            ->setCompanyAddressHouseNumber($debtor->getAddressHouse())
            ->setCompanyAddressStreet($debtor->getAddressStreet())
            ->setCompanyAddressPostalCode($debtor->getAddressPostalCode())
            ->setCompanyAddressCity($debtor->getAddressCity())
            ->setCompanyAddressCountry($debtor->getAddressCountry())
            ->setBankAccountIban($debtorPaymentDetails->getBankAccountIban())
            ->setBankAccountBic($debtorPaymentDetails->getBankAccountBic())
        ;
    }

    private function addInvoiceToOrder(OrderEntity $order, GetOrderResponse $response)
    {
        $orderPaymentDetails = $this->borscht->getOrderPaymentDetails($order->getPaymentId());
        $response
            ->setInvoiceNumber($order->getInvoiceNumber())
            ->setPayoutAmount($orderPaymentDetails->getPayoutAmount())
            ->setFeeRate($orderPaymentDetails->getFeeRate())
            ->setFeeAmount($orderPaymentDetails->getFeeAmount())
            ->setDueDate($orderPaymentDetails->getDueDate())
        ;
    }
}
