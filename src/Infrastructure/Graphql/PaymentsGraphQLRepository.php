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
        $response = $this->executeQuery('get_merchant_payments_by_uuid', $params);
        $this->logInfo('Graphql params, response', [$params, $response]);

        return $response;
    }
}
