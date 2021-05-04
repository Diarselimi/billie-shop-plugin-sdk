<?php

namespace App\Tests\Helpers;

use Faker\Factory;
use Ozean12\Money\Money;
use Ozean12\Money\Percent;
use Ozean12\Money\TaxedMoney\TaxedMoney;
use Ozean12\Money\TaxedMoney\TaxedMoneyFactory;

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
                    [$firstParam] = $methodReflection->getParameters();
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
            $valueType = $parameter->getType() ? $parameter->getType()->getName() : null;
            $faker = Factory::create('de_DE');

            $methodName = strtolower($faker->parse(str_replace('set', '', $methodName)));
            if (stripos($methodName, 'uuid') !== false) {
                $methodName = 'uuid';
            }

            try {
                $val = $faker->$methodName;
            } catch (\Exception $e) {
                switch ($valueType) {
                    case 'float':
                        $val = $faker->randomFloat(2);

                        break;
                    case TaxedMoney::class:
                        $val = TaxedMoneyFactory::create($gross = $faker->randomFloat(2), $gross - 10, 10);

                        break;
                    case Money::class:
                        $val = new Money($faker->randomFloat(2));

                        break;
                    case TaxedMoney::class:
                        $val = TaxedMoneyFactory::create($gross = $faker->randomFloat(2, 200, 500), $gross - 10, 10);

                        break;
                    case Percent::class:
                        $val = new Percent($faker->randomFloat(2, 10, 90));

                        break;
                    case 'int':
                        $val = $faker->randomNumber(5);

                        break;
                    case 'bool':
                        $val = $faker->randomElement([0, 1]);

                        break;
                    case 'DateTimeInterface':
                    case 'DateTime':
                        $val = new \DateTime($faker->date('Y-m-d H:i:s'));

                        break;
                    case 'string':
                        $val = $faker->text(10);

                        break;
                    default:
                        $val = new $valueType();
                        $this->fillObject($val, true);

                        break;
                }
            }

            $values[] = $val;
        }

        return $values;
    }
}
