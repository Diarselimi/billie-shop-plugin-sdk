<?php

declare(strict_types=1);

namespace App\Support\ParamConverter;

use App\Application\Exception\RequestValidationException;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class UuidParamConverter implements ParamConverterInterface
{
    public function apply(Request $request, ParamConverter $configuration): bool
    {
        $parameterName = $configuration->getName();
        $value = $request->attributes->get($parameterName);

        try {
            $uuid = Uuid::fromString($value);
        } catch (InvalidUuidStringException $exception) {
            throw RequestValidationException::createForInvalidValue(
                'Invalid UUID parameter provided',
                $parameterName,
                $value
            );
        }
        $request->attributes->set($parameterName, $uuid);

        return true;
    }

    public function supports(ParamConverter $configuration): bool
    {
        $class = $configuration->getClass();

        return Uuid::class === $class || UuidInterface::class === $class;
    }
}
