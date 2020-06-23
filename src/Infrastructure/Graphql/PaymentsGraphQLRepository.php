<?php

declare(strict_types=1);

namespace App\Infrastructure\Graphql;

use App\DomainModel\Payment\PaymentsRepositoryInterface;
use App\DomainModel\Payment\RequestDTO\SearchPaymentsDTO;
use App\Support\PaginatedCollection;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class PaymentsGraphQLRepository extends AbstractGraphQLRepository implements PaymentsRepositoryInterface, LoggingInterface
{
    use LoggingTrait;

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
            'keyword' => $paymentsDTO->getKeyword(),
        ];

        $countParams = [
            'merchantUuid' => $paymentsDTO->getMerchantPaymentUuid(),
            'paymentDebtorUuid' => $paymentsDTO->getPaymentDebtorUuid(),
            'transactionUuid' => $paymentsDTO->getTransactionUuid(),
            'keyword' => $paymentsDTO->getKeyword(),
        ];

        $countResult = $this->query('get_merchant_payments_total', $countParams);
        $total = $countResult[0]['total'] ?? 0;

        return new PaginatedCollection($this->query('get_merchant_payments', $params), $total);
    }

    public function getPaymentDetails(string $merchantPaymentUuid, string $transactionUuid): array
    {
        $params = [
            'merchantUuid' => $merchantPaymentUuid,
            'transactionUuid' => $transactionUuid,
        ];

        $response = $this->query('get_merchant_payment_details', $params);

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
            'keyword' => null,
        ];

        $result = $this->query('get_order_payments', $params);

        return new PaginatedCollection($result, count($result));
    }

    private function query(string $name, array $params): array
    {
        $response = $this->executeQuery($name, $params);
        $total = $response['total'] ?? count($response);
        $this->logInfo('GraphQL "' . $name . '" query', ['params' => $params, 'total_results' => $total, 'response' => $response]);

        return $response;
    }
}
