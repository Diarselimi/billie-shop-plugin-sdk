<?php

namespace App\Infrastructure\OpenApi\Routing;

use Doctrine\Common\Annotations\Reader;
use Illuminate\Support\Str;
use OpenApi\Annotations\Operation;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class OpenApiClassLoader implements LoaderInterface
{
    public const ROUTE_TYPE = 'openapi_annotation';

    private const CLASS_METHODS = ['execute', '__invoke'];

    protected $reader;

    /**
     * @var int
     */
    protected $defaultRouteIndex = 0;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Loads from annotations from a class.
     *
     * @param string      $class A class name
     * @param string|null $type  The resource type
     *
     * @return RouteCollection A RouteCollection instance
     *
     * @throws \InvalidArgumentException When route can't be parsed
     * @throws \ReflectionException
     */
    public function load($class, $type = null)
    {
        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
        }

        $class = new \ReflectionClass($class);
        if ($class->isAbstract()) {
            throw new \InvalidArgumentException(sprintf('Annotations from class "%s" cannot be read as it is abstract.', $class->getName()));
        }

        $collection = new RouteCollection();
        $collection->addResource(new FileResource($class->getFileName()));

        $methodName = $class->hasMethod('execute') ? 'execute' : ($class->hasMethod('__invoke') ? '__invoke' : null);
        if (!$methodName) {
            return $collection;
        }

        $method = $class->getMethod($methodName);
        $config = $this->readSymfonyRouteAnnotation($method) ?: $this->readSymfonyRouteAnnotation($class) ?: $this->getDefaultConfig();
        $this->defaultRouteIndex = 0;

        foreach ($this->reader->getClassAnnotations($class) as $annot) {
            if ($annot instanceof Operation) {
                $this->addRoute($collection, $annot, $config, $class, $method);
            }
        }
        foreach ($this->reader->getMethodAnnotations($method) as $annot) {
            if ($annot instanceof Operation) {
                $this->addRoute($collection, $annot, $config, $class, $method);
            }
        }

        return $collection;
    }

    protected function addRoute(RouteCollection $collection, Operation $annot, $config, \ReflectionClass $class, \ReflectionMethod $method)
    {
        $config['methods'] = [strtoupper($annot->method)];
        $config['name'] = $annot->operationId ?? $config['name'] ?? $this->getDefaultRouteName($class);
        $config['path'] = $annot->path;

        foreach ($method->getParameters() as $param) {
            if (isset($defaults[$param->name]) || !$param->isDefaultValueAvailable()) {
                continue;
            }
            if (preg_match(sprintf('/\{%s(?:<.*?>)?\}/', preg_quote($param->name)), $config['path'])) {
                $defaults[$param->name] = $param->getDefaultValue();

                break;
            }
        }

        $route = new Route(
            $config['path'],
            $config['defaults'],
            $config['requirements'],
            $config['options'],
            $config['host'],
            $config['schemes'],
            $config['methods'],
            $config['condition']
        );

        if ('__invoke' === $method->getName()) {
            $route->setDefault('_controller', $class->getName());
        } else {
            $route->setDefault('_controller', $class->getName() . '::' . $method->getName());
        }

        $collection->add('oa_'.$config['name'], $route);
    }

    public function supports($resource, $type = null)
    {
        return \is_string($resource) && preg_match('/^(?:\\\\?[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)+$/', $resource) && (!$type || self::ROUTE_TYPE === $type);
    }

    protected function getDefaultRouteName(\ReflectionClass $class): string
    {
        $name = str_replace('_controller', '', Str::snake($class->getShortName()));
        if ($this->defaultRouteIndex > 0) {
            $name .= '_' . $this->defaultRouteIndex;
        }
        ++$this->defaultRouteIndex;

        return $name;
    }

    protected function getDefaultConfig(): array
    {
        return [
            'path' => null,
            'requirements' => [],
            'options' => [],
            'defaults' => [],
            'schemes' => [],
            'methods' => [],
            'host' => '',
            'condition' => '',
            'name' => '',
        ];
    }

    protected function readSymfonyRouteAnnotation($reflectionObject): ?array
    {
        $config = $this->getDefaultConfig();

        $routeClass = \Symfony\Component\Routing\Annotation\Route::class;
        $annot = $reflectionObject instanceof \ReflectionClass ? $this->reader->getClassAnnotation($reflectionObject, $routeClass) :
            ($reflectionObject instanceof \ReflectionMethod ? $this->reader->getMethodAnnotation($reflectionObject, $routeClass) : null);

        if (!$annot) {
            return null;
        }

        if (null !== $annot->getName()) {
            $config['name'] = $annot->getName();
        }

        if (null !== $annot->getRequirements()) {
            $config['requirements'] = $annot->getRequirements();
        }

        if (null !== $annot->getOptions()) {
            $config['options'] = $annot->getOptions();
        }

        if (null !== $annot->getDefaults()) {
            $config['defaults'] = $annot->getDefaults();
        }

        if (null !== $annot->getSchemes()) {
            $config['schemes'] = $annot->getSchemes();
        }

        if (null !== $annot->getHost()) {
            $config['host'] = $annot->getHost();
        }

        if (null !== $annot->getCondition()) {
            $config['condition'] = $annot->getCondition();
        }

        foreach ($config['requirements'] as $placeholder => $requirement) {
            if (is_int($placeholder)) {
                @trigger_error(sprintf(
                    'A placeholder name must be a string (%d given). Did you forget to specify the placeholder 
                    key for the requirement "%s" of route "%s" in "%s"?',
                    $placeholder,
                    $requirement,
                    $config['name'],
                    $reflectionObject->getFileName() . ':' . $reflectionObject->getName()
                ), E_USER_DEPRECATED);
            }
        }
    }

    public function getResolver()
    {
    }

    public function setResolver(LoaderResolverInterface $resolver)
    {
    }
}
