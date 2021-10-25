<?php

namespace App\UserInterface\Http\KlarnaScheme\Authorize;

use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\OrderResponse\CheckoutAuthorizeOrderResponse;
use App\UserInterface\Http\KlarnaScheme\KlarnaResponse;

class AuthorizeResponse extends KlarnaResponse
{
    public function __construct(OrderContainer $orderContainer, CheckoutAuthorizeOrderResponse $orderResponse)
    {
        $response = [
            'payment_method' => [
                'ui' => [
                    'data' => $orderResponse->toArray(),
                    'show' => false,
                    'uri' => 'uri',
                ],
            ],
        ];

        if ($orderResponse->isAuthorized()) {
            $response = array_merge($response, [
                'customer_order_reference' => $orderContainer->getOrder()->getUuid(),
                'payment_method_reference' => $orderContainer->getOrder()->getUuid(),
                'result' => 'accepted',
            ]);
        } elseif ($orderResponse->isDeclinedFinally()) {
            $response['result'] = 'rejected';
        } else {
            $response['result'] = 'user_action_required';
        }

        parent::__construct($response);
    }
}
