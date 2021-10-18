<?php

namespace App\Infrastructure\Repository;

trait HydratorTrait
{
    /**
     * @template T
     * @param  class-string<T>|string $class
     * @return T
     */
    protected function hydrate(string $class, array $map): object
    {
        $rClass = new \ReflectionClass($class);
        $instance = $rClass->newInstanceWithoutConstructor();

        foreach ($map as $property => $value) {
            $rProp = $rClass->getProperty($property);
            $rProp->setAccessible(true);
            $rProp->setValue($instance, $value);
        }

        return $instance;
    }
}
