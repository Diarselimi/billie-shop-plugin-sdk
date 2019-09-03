<?php

namespace App\DomainModel\MerchantPayment;

use App\Application\UseCase\GetMerchantPayments\GetMerchantPaymentsResponse;

class MerchantPaymentResponseFactory
{
    public function createFromGraphql(array $data): GetMerchantPaymentsResponse
    {
        return (new GetMerchantPaymentsResponse())
            ->setItems($data)
            ->setTotal(count($data))
            ;
    }
}
