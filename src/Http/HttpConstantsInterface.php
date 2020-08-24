<?php

namespace App\Http;

interface HttpConstantsInterface
{
    public const REQUEST_HEADER_API_KEY = 'X-Api-Key';

    public const REQUEST_HEADER_AUTHORIZATION = 'Authorization';

    public const REQUEST_ATTRIBUTE_MERCHANT_ID = 'merchant_id';

    public const REQUEST_ATTRIBUTE_CHECKOUT_SESSION_ID = 'sessionUuid';

    public const REQUEST_ATTRIBUTE_CREATION_SOURCE = 'creation_source';
}
