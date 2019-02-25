<?php

namespace App\Http;

interface HttpConstantsInterface
{
    const REQUEST_HEADER_API_USER = 'X-Api-User';

    const ROUTE_HEALTH_CHECK = 'health_check';

    const ROUTE_MARK_ORDER_AS_FRAUD = 'mark_order_as_fraud';
}
