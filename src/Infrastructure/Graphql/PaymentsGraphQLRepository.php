<?php

declare(strict_types=1);

namespace App\Infrastructure\Graphql;

use App\DomainModel\Payment\PaymentsRepositoryInterface;
use App\DomainModel\Payment\RequestDTO\SearchPaymentsDTO;
use App\Support\PaginatedCollection;

class PaymentsGraphQLRepository extends AbstractGraphQLRepository implements PaymentsRepositoryInterface
{
    private const GET_MERCHANT_PAYMENTS_QUERY = 'get_merchant_payments';

    private const GET_MERCHANT_PAYMENTS_TOTAL_QUERY = 'get_merchant_payments_total';

    private const GET_PAYMENT_DETAILS_QUERY = 'get_merchant_payment_details';

    private const GET_ORDER_PAYMENTS_QUERY = 'get_order_payments';

    public function searchMerchantPayments(SearchPaymentsDTO $paymentsDTO): PaginatedCollection
    {
        $params = [
            'merchantUuid' => $paymentsDTO->getMerchantPaymentUuid(),
            'paymentDebtorUuid' => $paymentsDTO->getPaymentDebtorUuid(),
            'transactionUuid' => $paymentsDTO->getTransactionUuid(),
            'offset' => (string) $paymentsDTO->getOffset(),
            'limit' => (string) $paymentsDTO->getLimit(),
            'sortBy' => $paymentsDTO->getSortBy(),
            'sortDirection' => $paymentsDTO->getSortDirection(),
            'searchString' => $paymentsDTO->getSearchString(),
            'searchCompanyString' => $paymentsDTO->getSearchCompanyString(),
        ];

        $countParams = [
            'merchantUuid' => $paymentsDTO->getMerchantPaymentUuid(),
            'paymentDebtorUuid' => $paymentsDTO->getPaymentDebtorUuid(),
            'transactionUuid' => $paymentsDTO->getTransactionUuid(),
            'searchString' => $paymentsDTO->getSearchString(),
            'searchCompanyString' => $paymentsDTO->getSearchCompanyString(),
        ];

        $countResult = $this->query(self::GET_MERCHANT_PAYMENTS_TOTAL_QUERY, $countParams);
        $total = $countResult[0]['total'] ?? 0;

        return new PaginatedCollection($this->query(self::GET_MERCHANT_PAYMENTS_QUERY, $params), $total);
    }

    public function getPaymentDetails(string $merchantPaymentUuid, string $transactionUuid): array
    {
        $params = [
            'merchantUuid' => $merchantPaymentUuid,
            'transactionUuid' => $transactionUuid,
        ];

        $response = $this->query(self::GET_PAYMENT_DETAILS_QUERY, $params);

        return empty($response) ? [] : $response[0];
    }

    public function getOrderPayments(string $orderPaymentUuid): PaginatedCollection
    {
        $params = [
            'ticketUuid' => $orderPaymentUuid,
            'offset' => '0',
            'limit' => '100',
            'sortBy' => 'transaction_date',
            'sortDirection' => 'desc',
            'searchString' => null,
        ];

        $result = $this->query(self::GET_ORDER_PAYMENTS_QUERY, $params);

        return new PaginatedCollection($result, count($result));
    }
}
