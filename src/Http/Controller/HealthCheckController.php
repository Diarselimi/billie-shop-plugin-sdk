<?php

namespace App\Http\Controller;

use Symfony\Component\HttpFoundation\Response;

class HealthCheckController
{
    public function execute()
    {
        return new Response();
    }
}
