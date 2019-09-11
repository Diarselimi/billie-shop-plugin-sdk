<?php

namespace App\DomainModel\MerchantPayment;

use App\Application\UseCase\GetMerchantPayments\GetMerchantPaymentsResponse;

class MerchantPaymentResponseFactory
{
    public function createFromGraphql(array $result): GetMerchantPaymentsResponse
    {
        $result['items'] = array_map(function (array $item) {
            $item['is_allocated'] = boolval($item['is_allocated']);

            return $item;
        }, $result['items']);

        return (new GetMerchantPaymentsResponse())
            ->setItems($result['items'])
            ->setTotal($result['total']);
    }
}
