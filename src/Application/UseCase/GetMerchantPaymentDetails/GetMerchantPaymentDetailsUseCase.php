<?php

namespace App\Application\UseCase\GetMerchantPaymentDetails;

use App\Application\Exception\MerchantDebtorNotFoundException;
use App\Application\UseCase\GetMerchant\MerchantNotFoundException;
use App\DomainModel\Invoice\InvoiceServiceInterface;
use App\DomainModel\Merchant\MerchantRepository;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\OrderInvoice\OrderInvoiceRepositoryInterface;
use App\DomainModel\Payment\PaymentsRepositoryInterface;
use App\DomainModel\PaymentMethod\BankTransactionPaymentMethodResolver;
use Ozean12\Borscht\Client\DomainModel\BankTransaction\BankTransactionTicket;
use Ozean12\Borscht\Client\DomainModel\BorschtClientInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class GetMerchantPaymentDetailsUseCase
{
    private MerchantRepository $merchantRepository;

    private PaymentsRepositoryInterface $paymentsRepository;

    private BankTransactionPaymentMethodResolver $paymentMethodResolver;

    private MerchantDebtorRepositoryInterface $merchantDebtorRepository;

    private OrderInvoiceRepositoryInterface $orderInvoiceRepository;

    private BorschtClientInterface $borschtService;

    private InvoiceServiceInterface $invoiceButlerClient;

    public function __construct(
        MerchantRepository $merchantRepository,
        PaymentsRepositoryInterface $paymentsRepository,
        BankTransactionPaymentMethodResolver $paymentMethodResolver,
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        OrderInvoiceRepositoryInterface $orderInvoiceRepository,
        BorschtClientInterface $borschtService,
        InvoiceServiceInterface $invoiceService
    ) {
        $this->merchantRepository = $merchantRepository;
        $this->paymentsRepository = $paymentsRepository;
        $this->paymentMethodResolver = $paymentMethodResolver;
        $this->merchantDebtorRepository = $merchantDebtorRepository;
        $this->orderInvoiceRepository = $orderInvoiceRepository;
        $this->borschtService = $borschtService;
        $this->invoiceButlerClient = $invoiceService;
    }

    public function execute(GetMerchantPaymentDetailsRequest $request): GetMerchantPaymentDetailsResponse
    {
        $merchant = $this->merchantRepository->getOneById($request->getMerchantId());

        if ($merchant === null) {
            throw new MerchantNotFoundException();
        }

        $transactionDetails = $this->paymentsRepository->getPaymentDetails(
            $merchant->getPaymentUuid(),
            $request->getTransactionUuid()
        );

        $transaction = $this->borschtService->getBankTransactionDetails($request->getTransactionUuid());

        $invoiceUuids =
            $transaction->getTickets()->map(
                fn (BankTransactionTicket $ticket) => $ticket->getUuid()->toString()
            )->toArray();

        $invoices = $this->invoiceButlerClient->getByUuids($invoiceUuids);
        $orderInvoices = $this->orderInvoiceRepository->getByInvoiceCollection($invoices);
        $orderInvoices->assignInvoices($invoices);

        $debtorPaymentUuid = $this->getDebtorPaymentUuid($transactionDetails->getMerchantDebtorUuid());

        $paymentMethod = $this->paymentMethodResolver->getPaymentMethod(
            $transaction,
            $debtorPaymentUuid,
        );

        return new GetMerchantPaymentDetailsResponse($transactionDetails, $paymentMethod, $orderInvoices, $transaction);
    }

    private function getDebtorPaymentUuid(?UuidInterface $merchantDebtorUuid): ?UuidInterface
    {
        if ($merchantDebtorUuid === null) {
            return null;
        }

        $merchantDebtor = $this->merchantDebtorRepository->getOneByUuid($merchantDebtorUuid);
        if ($merchantDebtor === null) {
            throw new MerchantDebtorNotFoundException();
        }

        if ($merchantDebtor->getPaymentDebtorId() === null) {
            return null;
        }

        return Uuid::fromString($merchantDebtor->getPaymentDebtorId());
    }
}
