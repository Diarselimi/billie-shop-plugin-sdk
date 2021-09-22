<?php

namespace App\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class IntegrationTestCase extends KernelTestCase
{
    protected function replaceService(string $id, object $service): void
    {
        $this->getContainer()->set($id, $service);
    }

    protected function loadService(string $id): object
    {
        return $this->getContainer()->get($id);
    }

    protected function getContainer(): ContainerInterface
    {
        return parent::$container;
    }

    protected function setUp(): void
    {
        self::bootKernel();
    }
}
