<?php

declare(strict_types=1);

namespace App\DomainModel\MerchantDebtor;

use App\Support\PaginatedCollection;

/**
 * TESTING purpose only!
 * Since we don't have easy way to mock data from graphql endpoints, we are doing it through dummy class
 */
class DummySearchMerchantDebtorsRepository implements SearchMerchantDebtorsRepositoryInterface
{
    public function searchMerchantDebtors(SearchMerchantDebtorsDTO $dto): PaginatedCollection
    {
        return new PaginatedCollection(
            [
                [
                    "id" => "1",
                    "merchant_id" => 1,
                    "uuid" => "ad74bbc4-509e-47d5-9b50-a0320ce3d715",
                    "debtor_id" => 1,
                    "company_uuid" => "c7be46c0-e049-4312-b274-258ec5aeeb70",
                    "payment_debtor_id" => "test",
                    "score_thresholds_configuration_id" => null,
                    "created_at" => "2019-01-01T12:00:00Z",
                    "updated_at" => "2019-01-01T12:00:00Z",
                    "debtor_information_change_request_state" => null,
                ],
            ],
            1
        );
    }
}
