<?php

declare(strict_types=1);

namespace spec\App\Http\EventSubscriber;

use App\Http\ApiError\ApiErrorResponse;
use App\Http\ApiError\ApiErrorResponseFactory;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class ExceptionSubscriberSpec extends ObjectBehavior
{
    public function let(ApiErrorResponseFactory $errorResponseFactory): void
    {
        $this->beConstructedWith(...func_get_args());
    }

    public function it_should_set_response_on_event(
        ApiErrorResponseFactory $errorResponseFactory,
        GetResponseForExceptionEvent $event,
        ApiErrorResponse $response
    ): void {
        $exception = new \Exception();
        $event->getException()->willReturn($exception);
        $errorResponseFactory->createFromException($exception)->willReturn($response);
        $response->getStatusCode()->willReturn(404);

        $event->setResponse($response)->shouldBeCalledOnce();

        $this->onException($event);
    }
}
