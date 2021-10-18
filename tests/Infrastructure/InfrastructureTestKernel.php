<?php

namespace App\Tests\Infrastructure;

use App\Infrastructure\Repository\CheckoutSessionPdoRepository;
use App\Kernel;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class InfrastructureTestKernel extends Kernel
{
    private const SERVICES_TO_BE_PUBLIC = [
        CheckoutSessionPdoRepository::class,
    ];

    public function __construct()
    {
        parent::__construct('test', false);

        $this->boot();
    }

    /**
     * @template T
     * @param  class-string<T>|string $id
     * @return T
     */
    public function loadFromContainer(string $id)
    {
        return $this->getContainer()->get($id);
    }

    protected function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass($this->createMarkPublicCompilerPass());

        parent::build($container);
    }

    private function createMarkPublicCompilerPass(): CompilerPassInterface
    {
        return new class(self::SERVICES_TO_BE_PUBLIC) implements CompilerPassInterface {
            private array $ids;

            public function __construct(array $ids)
            {
                $this->ids = $ids;
            }

            public function process(ContainerBuilder $container): void
            {
                foreach ($this->ids as $id) {
                    $container->getDefinition($id)->setPublic(true);
                    $container->getDefinition($id)->setLazy(false);
                }
            }
        };
    }
}
