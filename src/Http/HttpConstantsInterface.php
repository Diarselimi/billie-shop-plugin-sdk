<?php

namespace App\Http;

interface HttpConstantsInterface
{
    const REQUEST_HEADER_API_USER = 'X-Api-User';
    const REQUEST_HEADER_RID = 'X-Request-Id';

    const ROUTE_HEALTH_CHECK = 'health_check';
}
