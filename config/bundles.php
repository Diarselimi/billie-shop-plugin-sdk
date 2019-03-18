<?php

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    EightPoints\Bundle\GuzzleBundle\EightPointsGuzzleBundle::class => ['all' => true],
    Symfony\Bundle\MonologBundle\MonologBundle::class => ['all' => true],
    OldSound\RabbitMqBundle\OldSoundRabbitMqBundle::class => ['all' => true],
    Aws\Symfony\AwsBundle::class => ['all' => true], // TODO: change it to 'dev',
    Billie\MonitoringBundle\BillieMonitoringBundle::class => ['all' => true],
    Billie\PdoBundle\BilliePdoBundle::class => ['all' => true],
];
