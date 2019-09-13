<?php

declare(strict_types=1);

namespace App\Infrastructure\Graphql;

use App\DomainModel\Payment\PaymentsRepositoryInterface;
use App\DomainModel\Payment\RequestDTO\SearchPaymentsDTO;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class PaymentsGraphQLRepository extends AbstractGraphQLRepository implements PaymentsRepositoryInterface, LoggingInterface
{
    use LoggingTrait;

    public function search(SearchPaymentsDTO $paymentsDTO): array
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

        $countResult = $this->executeQuery('get_merchant_payments_by_uuid_total', $countParams);

        $response = [
            'items' => $this->executeQuery('get_merchant_payments_by_uuid', $params),
            'total' => $countResult[0]['total'] ?? 0,
        ];

        $this->logInfo('Graphql params, response', [$params, $response]);

        return $response;
    }
}
