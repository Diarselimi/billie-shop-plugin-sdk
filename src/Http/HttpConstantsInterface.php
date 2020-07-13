<?php

namespace App\Http;

interface HttpConstantsInterface
{
    const REQUEST_HEADER_API_KEY = 'X-Api-Key';

    const REQUEST_HEADER_AUTHORIZATION = 'Authorization';

    const REQUEST_ATTRIBUTE_MERCHANT_ID = 'merchant_id';

    const REQUEST_ATTRIBUTE_CHECKOUT_SESSION_ID = 'sessionUuid';

    const REQUEST_ATTRIBUTE_CREATION_SOURCE = 'creation_source';
}
