<?php

namespace App\Http\EventSubscriber;

use App\DomainModel\ArrayableInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class JsonConverterSubscriber implements EventSubscriberInterface
{
    public function onRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if (!in_array($request->getMethod(), [Request::METHOD_POST, Request::METHOD_PATCH, Request::METHOD_PUT])
            || !$request->headers->has('Content-Type')
            || !$request->headers->get('Content-Type') === 'application/json'
            || !$request->getContent()
        ) {
            return;
        }

        $json = $request->getContent();
        $requestData = json_decode($json, true);

        if (!is_array($requestData)) {
            throw new BadRequestHttpException('Malformed request');
        }

        $requestData = $this->sanitizeRecursive($requestData);

        $request->request->add($requestData);
    }

    private function sanitizeRecursive(array $data): array
    {
        foreach ($data as $k => $value) {
            if (is_array($value)) {
                $data[$k] = $this->sanitizeRecursive($value);

                continue;
            }

            $data[$k] = is_string($value) ? trim($value) : $value;
        }

        return $data;
    }

    public function onView(GetResponseForControllerResultEvent $event)
    {
        $response = $event->getControllerResult();

        if ($response === null) {
            $event->setResponse(new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT));
        }

        if ($response instanceof ArrayableInterface) {
            $event->setResponse(new JsonResponse($response->toArray()));
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onRequest',
            KernelEvents::VIEW => 'onView',
        ];
    }
}
