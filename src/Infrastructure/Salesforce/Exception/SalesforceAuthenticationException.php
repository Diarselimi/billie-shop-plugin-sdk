<?php

namespace App\Infrastructure\Salesforce\Exception;

class SalesforceAuthenticationException extends SalesforceException
{
    protected $message = 'Salesforce authentication failed. Invalid authentication key provided.';
}
