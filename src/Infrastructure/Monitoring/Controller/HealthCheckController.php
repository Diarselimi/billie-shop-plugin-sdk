<?php

namespace App\Infrastructure\Monitoring\Controller;

use Symfony\Component\HttpFoundation\Response;

class HealthCheckController
{
    public function execute(string $projectName)
    {
        return new Response($projectName.' is alive');
    }
}
