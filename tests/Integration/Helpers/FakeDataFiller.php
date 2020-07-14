<?php

namespace App\Tests\Integration\Helpers;

use Faker\Factory;

trait FakeDataFiller
{
    public function fillObject($object, $fillOptionalSetters = false)
    {
        if (!is_object($object)) {
            return;
        }

        $methods = get_class_methods(get_class($object));
        $objectReflection = new \ReflectionClass(get_class($object));

        foreach ($methods as $method) {
            if (str_contains($method, 'set')) {
                try {
                    $methodReflection = $objectReflection->getMethod($method);
                    list($firstParam) = $methodReflection->getParameters();
                } catch (\Exception $e) {
                    continue;
                }

                if ($firstParam->allowsNull() && $fillOptionalSetters === false) {
                    continue; //assuming this method is optional.
                }
                $object->$method(...$this->getRandomParamValues($methodReflection));
            }
        }
    }

    private function getRandomParamValues(\ReflectionMethod $method)
    {
        $methodName = $method->getName();
        $values = [];

        foreach ($method->getParameters() as $parameter) {
            $valueType = (string) $parameter->getType();
            $faker = Factory::create('de_DE');

            $methodName = strtolower($faker->parse(str_replace('set', '', $methodName)));

            try {
                $val = $faker->$methodName;
            } catch (\Exception $e) {
                switch ($valueType) {
                    case 'float':
                        $val = $faker->randomFloat(2);

                        break;
                    case 'int':
                        $val = $faker->randomNumber(5);

                        break;
                    case 'bool':
                        $val = $faker->randomElement([0, 1]);

                        break;
                    case 'DateTime':
                        $val = new \DateTime($faker->date('Y-m-d H:i:s'));

                        break;
                    case 'string':
                        $val = $faker->text(10);

                        break;
                    default:
                        $val = new $valueType();
                        $this->fillObject($valueType);

                        break;
                }
            }

            $values[] = $val;
        }

        return $values;
    }
}
