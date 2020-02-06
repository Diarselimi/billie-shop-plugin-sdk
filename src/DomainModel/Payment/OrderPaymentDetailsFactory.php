<?php

namespace App\DomainModel\Payment;

class OrderPaymentDetailsFactory
{
    public function createFromBorschtResponse(array $response): OrderPaymentDetailsDTO
    {
        return (new OrderPaymentDetailsDTO())
            ->setId($response['id'])
            ->setState($response['state'])
            ->setPayoutAmount($response['payout_amount'])
            ->setOutstandingAmount($response['outstanding_amount'])
            ->setFeeAmount($response['fee_amount'])
            ->setFeeRate($response['fee_rate'])
            ->setDueDate(new \DateTime($response['due_date']))
            ->setOutstandingAmountInvoiceCancellation($response['outstanding_amount_invoice_cancellation'])
            ->setOutstandingAmountMerchantPayment($response['outstanding_amount_merchant_payment'])
        ;
    }

    /**
     * @param  array                    $ordersResponse
     * @return OrderPaymentDetailsDTO[]
     * @throws \Exception
     */
    public function createFromBorschtArrayResponse(array $ordersResponse): array
    {
        $ordersResponses = [];
        foreach ($ordersResponse as $orderResponse) {
            $ordersResponses[$ordersResponse['id']] = $this->createFromBorschtResponse($orderResponse);
        }

        return $ordersResponses;
    }
}
