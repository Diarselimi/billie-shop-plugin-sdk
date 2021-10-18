<?php

namespace App\Tests\Infrastructure;

trait ServiceLoaderTrait
{
    private ?InfrastructureTestKernel $kernel = null;

    /**
     * @template T
     * @param  class-string<T>|string $id
     * @return T
     */
    protected function loadService(string $id): object
    {
        return $this->loadKernel()->loadFromContainer($id);
    }

    protected function loadKernel(): InfrastructureTestKernel
    {
        return $this->kernel ??= new InfrastructureTestKernel();
    }
}
