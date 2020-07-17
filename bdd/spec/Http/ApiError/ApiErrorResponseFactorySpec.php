<?php

declare(strict_types=1);

namespace spec\App\Http\ApiError;

use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

class ApiErrorResponseFactorySpec extends ObjectBehavior
{
    public function let(CamelCaseToSnakeCaseNameConverter $propertyNameConverter): void
    {
        $this->beConstructedWith(...func_get_args());
    }

    public function it_should_return_404_when_not_found(): void
    {
        $this->createFromException(new NotFoundHttpException())->getStatusCode()->shouldBe(404);
    }
}
