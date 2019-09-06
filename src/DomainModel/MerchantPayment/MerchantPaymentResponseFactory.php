<?php

namespace App\DomainModel\MerchantPayment;

use App\Application\UseCase\GetMerchantPayments\GetMerchantPaymentsResponse;

class MerchantPaymentResponseFactory
{
    public function createFromGraphql(array $data): GetMerchantPaymentsResponse
    {
        //TODO: Create the DTO and remove this from here
        $data = array_map(function (array $data) {
            $data['is_allocated'] = !!$data['is_allocated'];

            return $data;
        }, $data);

        return (new GetMerchantPaymentsResponse())
            ->setItems($data)
            ->setTotal(count($data))
            ;
    }
}
