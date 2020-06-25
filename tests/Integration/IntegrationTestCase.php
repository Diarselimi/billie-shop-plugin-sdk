<?php

namespace App\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class IntegrationTestCase extends KernelTestCase
{
    protected function getContainer(): ContainerInterface
    {
        return parent::$container;
    }

    public function setUp(): void
    {
        self::bootKernel();
    }
}
