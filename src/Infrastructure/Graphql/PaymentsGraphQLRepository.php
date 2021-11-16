<?php

declare(strict_types=1);

namespace App\Infrastructure\Graphql;

use App\DomainModel\Payment\BankTransactionDetails;
use App\DomainModel\Payment\BankTransactionDetailsFactory;
use App\DomainModel\Payment\BankTransactionNotFoundException;
use App\DomainModel\Payment\PaymentsRepositoryInterface;
use App\DomainModel\Payment\RequestDTO\SearchPaymentsDTO;
use App\Support\PaginatedCollection;
use Ozean12\GraphQLBundle\GraphQLInterface;

class PaymentsGraphQLRepository extends AbstractGraphQLRepository implements PaymentsRepositoryInterface
{
    private const GET_MERCHANT_PAYMENTS_QUERY = 'get_merchant_payments';

    private const GET_MERCHANT_PAYMENTS_TOTAL_QUERY = 'get_merchant_payments_total';

    private const GET_PAYMENT_DETAILS_QUERY = 'get_merchant_payment_details';

    private const GET_TICKET_PAYMENTS_QUERY = 'get_ticket_payments';

    private BankTransactionDetailsFactory $transactionDetailsFactory;

    public function __construct(GraphQLInterface $graphQL, BankTransactionDetailsFactory $transactionDetailsFactory)
    {
        parent::__construct($graphQL);

        $this->transactionDetailsFactory = $transactionDetailsFactory;
    }

    public function searchMerchantPayments(SearchPaymentsDTO $paymentsDTO): PaginatedCollection
    {
        $params = [
            'merchantUuid' => $paymentsDTO->getMerchantPaymentUuid(),
            'paymentDebtorUuid' => $paymentsDTO->getPaymentDebtorUuid(),
            'transactionUuid' => $paymentsDTO->getTransactionUuid(),
            'isAllocated' => $paymentsDTO->isAllocated() === null ? null : (int) $paymentsDTO->isAllocated(),
            'isOverpayment' => $paymentsDTO->isOverpayment() === null ? null : (int) $paymentsDTO->isOverpayment(),
            'offset' => $paymentsDTO->getOffset(),
            'limit' => $paymentsDTO->getLimit(),
            'sortBy' => $paymentsDTO->getSortBy(),
            'sortDirection' => $paymentsDTO->getSortDirection(),
            'searchString' => $paymentsDTO->getSearchString(),
            'searchCompanyString' => null,
        ];

        $countParams = [
            'merchantUuid' => $paymentsDTO->getMerchantPaymentUuid(),
            'paymentDebtorUuid' => $paymentsDTO->getPaymentDebtorUuid(),
            'transactionUuid' => $paymentsDTO->getTransactionUuid(),
            'isAllocated' => $paymentsDTO->isAllocated() === null ? null : $this->boolToString(
                $paymentsDTO->isAllocated()
            ),
            'isOverpayment' => $paymentsDTO->isOverpayment() === null ? null : $this->boolToString(
                $paymentsDTO->isOverpayment()
            ),
            'searchString' => $paymentsDTO->getSearchString(),
            'searchCompanyString' => null,
        ];

        $countResult = $this->query(self::GET_MERCHANT_PAYMENTS_TOTAL_QUERY, $countParams);
        $total = $countResult[0]['total'] ?? 0;

        return new PaginatedCollection($this->query(self::GET_MERCHANT_PAYMENTS_QUERY, $params), $total);
    }

    public function getPaymentDetails(string $merchantPaymentUuid, string $transactionUuid): BankTransactionDetails
    {
        $params = [
            'merchantUuid' => $merchantPaymentUuid,
            'transactionUuid' => $transactionUuid,
        ];

        $response = $this->query(self::GET_PAYMENT_DETAILS_QUERY, $params);

        if (empty($response)) {
            throw new BankTransactionNotFoundException();
        }

        return $this->transactionDetailsFactory->fromArray($response[0]);
    }

    public function getTicketPayments(string $paymentTicketUuid): PaginatedCollection
    {
        $params = [
            'ticketUuid' => $paymentTicketUuid,
            'offset' => '0',
            'limit' => '100',
            'sortBy' => 'transaction_date',
            'sortDirection' => 'desc',
            'searchString' => null,
        ];

        $result = $this->query(self::GET_TICKET_PAYMENTS_QUERY, $params);

        return new PaginatedCollection($result, count($result));
    }

    private function boolToString(bool $bool): string
    {
        return $bool ? '1' : '0';
    }
}
