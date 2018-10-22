<?php

namespace App\Application\UseCase\GetOrder;

use App\Application\PaellaCoreCriticalException;
use App\DomainModel\Address\AddressRepositoryInterface;
use App\DomainModel\Alfred\AlfredInterface;
use App\DomainModel\Borscht\BorschtInterface;
use App\DomainModel\DebtorExternalData\DebtorExternalDataRepositoryInterface;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\Order\OrderDeclinedReasonsMapper;
use Symfony\Component\HttpFoundation\Response;

class GetOrderUseCase
{
    private $orderRepository;
    private $merchantDebtorRepository;
    private $addressRepository;
    private $debtorExternalDataRepository;
    private $alfred;
    private $borscht;
    private $orderStateManager;
    private $declinedReasonsMapper;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        AddressRepositoryInterface $addressRepository,
        DebtorExternalDataRepositoryInterface $debtorExternalDataRepository,
        AlfredInterface $alfred,
        BorschtInterface $borscht,
        OrderStateManager $orderStateManager,
        OrderDeclinedReasonsMapper $declinedReasonsMapper
    ) {
        $this->orderRepository = $orderRepository;
        $this->merchantDebtorRepository = $merchantDebtorRepository;
        $this->addressRepository = $addressRepository;
        $this->debtorExternalDataRepository = $debtorExternalDataRepository;
        $this->alfred = $alfred;
        $this->borscht = $borscht;
        $this->orderStateManager = $orderStateManager;
        $this->declinedReasonsMapper = $declinedReasonsMapper;
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
            ->setDebtorExternalDataAddressPostalCode($debtorAddress['postal_code'])
            ->setDebtorExternalDataAddressStreet($debtorAddress['street'])
            ->setDebtorExternalDataAddressHouse($debtorAddress['house'])
            ->setDebtorExternalDataCompanyName($debtorData['name'])
            ->setDebtorExternalDataIndustrySector($debtorData['industry_sector'])
        ;

        if ($order->getMerchantDebtorId()) {
            $this->addCompanyToOrder($order, $response);
        }

        if ($this->orderStateManager->wasShipped($order)) {
            $this->addInvoiceToOrder($order, $response);
        }

        if ($this->orderStateManager->isDeclined($order)) {
            $response->setReasons($this->declinedReasonsMapper->mapReasons($order));
        }

        return $response;
    }

    private function addCompanyToOrder(OrderEntity $order, GetOrderResponse $response)
    {
        $company = $this->merchantDebtorRepository->getOneById($order->getMerchantDebtorId());
        $debtor = $this->alfred->getDebtor($company->getDebtorId());
        $debtorPaymentDetails = $this->borscht->getDebtorPaymentDetails($company->getPaymentDebtorId());

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
