<?php

declare(strict_types=1);

namespace App\Application\UseCase\GetInvoice;

use App\Application\Exception\InvoiceNotFoundException;
use App\Application\UseCase\GetInvoice\Factory\GetInvoiceResponseFactory;
use App\DomainModel\Invoice\InvoiceServiceInterface;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\PaymentMethod\BankNameDecorator;
use Ozean12\InvoiceButler\Client\DomainModel\InvoiceButlerClientInterface;

class GetInvoiceUseCase
{
    private InvoiceServiceInterface $legacyInvoiceButler;

    private GetInvoiceResponseFactory $responseFactory;

    private OrderRepositoryInterface $orderRepository;

    private InvoiceButlerClientInterface $invoiceButler;

    private OrderContainerFactory $orderContainerFactory;

    private BankNameDecorator $bankNameDecorator;

    public function __construct(
        InvoiceServiceInterface $legacyInvoiceButler,
        GetInvoiceResponseFactory $responseFactory,
        OrderRepositoryInterface $orderRepository,
        OrderContainerFactory $orderContainerFactory,
        InvoiceButlerClientInterface $invoiceButler,
        BankNameDecorator $bankNameDecorator
    ) {
        $this->legacyInvoiceButler = $legacyInvoiceButler;
        $this->responseFactory = $responseFactory;
        $this->orderRepository = $orderRepository;
        $this->orderContainerFactory = $orderContainerFactory;
        $this->invoiceButler = $invoiceButler;
        $this->bankNameDecorator = $bankNameDecorator;
    }

    public function execute(GetInvoiceRequest $request): GetInvoiceResponse
    {
        $order = $this->orderRepository->getByInvoiceAndMerchant($request->getUuid()->toString(), $request->getMerchantId());
        if ($order === null) {
            throw new InvoiceNotFoundException();
        }

        $invoice = $this->legacyInvoiceButler->getOneByUuid($request->getUuid()->toString());
        if ($invoice === null) {
            throw new InvoiceNotFoundException();
        }

        $orders = $this->orderRepository->getByInvoice($invoice->getUuid());
        $orderContainers = array_map(
            fn (OrderEntity $order) => $this->orderContainerFactory->createFromOrderEntity($order),
            $orders
        );

        $clientPaymentMethodCollection = $this->invoiceButler->accountsReceivableGetPaymentMethods($request->getUuid());
        $paymentMethodCollection = $this->bankNameDecorator->addBankName($clientPaymentMethodCollection);

        return $this->responseFactory->create($invoice, $orderContainers, $paymentMethodCollection);
    }
}
