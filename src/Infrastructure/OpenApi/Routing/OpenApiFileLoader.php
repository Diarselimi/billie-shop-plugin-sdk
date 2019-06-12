<?php

namespace App\Infrastructure\OpenApi\Routing;

use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Routing\Loader\AnnotationFileLoader;
use Symfony\Component\Routing\RouteCollection;

class OpenApiFileLoader extends AnnotationFileLoader
{
    protected $loader;

    /**
     * @param FileLocatorInterface $locator
     * @param LoaderInterface      $loader
     */
    public function __construct(FileLocatorInterface $locator, LoaderInterface $loader)
    {
        if (!\function_exists('token_get_all')) {
            throw new \LogicException('The Tokenizer extension is required for the routing annotation loaders.');
        }

        $this->locator = $locator;
        $this->loader = $loader;
    }

    /**
     * Loads from annotations from a file.
     *
     * @param string      $file A PHP file path
     * @param string|null $type The resource type
     *
     * @return RouteCollection|null A RouteCollection instance
     *
     * @throws \InvalidArgumentException When the file does not exist or its routes cannot be parsed
     * @throws \ReflectionException
     */
    public function load($file, $type = null)
    {
        $path = $this->locator->locate($file);

        $collection = new RouteCollection();
        if ($class = $this->findClass($path)) {
            $refl = new \ReflectionClass($class);
            if ($refl->isAbstract()) {
                return null;
            }

            $collection->addResource(new FileResource($path));
            $collection->addCollection($this->loader->load($class, $type));
        }

        // PHP 7 memory manager will not release after token_get_all(), see https://bugs.php.net/70098
        gc_mem_caches();

        return $collection;
    }

    public function supports($resource, $type = null)
    {
        return \is_string($resource) && 'php' === pathinfo($resource, PATHINFO_EXTENSION) &&
            (!$type || OpenApiClassLoader::ROUTE_TYPE === $type);
    }
}
