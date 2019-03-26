<?php

namespace App\Test;

use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use EightPoints\Bundle\GuzzleBundle\Events\GuzzleEvents;
use EightPoints\Bundle\GuzzleBundle\Events\PreTransactionEvent;
use GuzzleHttp\Psr7\Uri;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class TestGuzzleSubscriber implements EventSubscriberInterface, LoggingInterface
{
    use LoggingTrait;

    private const HEADER_NAME = 'X-Test-Id';

    private $request;

    private $mockServerUrl;

    private $servicesToMock;

    private $servicesToProxyHeader;

    private $cliTestId;

    public function __construct(
        RequestStack $request,
        string $mockServerUrl,
        array $servicesToMock,
        array $servicesToProxyHeader,
        ?string $cliTestId
    ) {
        $this->request = $request->getCurrentRequest();
        $this->mockServerUrl = $mockServerUrl;
        $this->servicesToMock = $servicesToMock;
        $this->servicesToProxyHeader = $servicesToProxyHeader;
        $this->cliTestId = $cliTestId;
    }

    public function onPreTransaction(PreTransactionEvent $event)
    {
        $testId = $this->request
            ? $this->request->headers->get(self::HEADER_NAME)
            : $this->cliTestId
        ;

        if (!$testId) {
            return;
        }

        $service = $event->getServiceName();
        $this->log("Service $service check");
        if (!\in_array($service, $this->servicesToMock) && !\in_array($service, $this->servicesToProxyHeader)) {
            $this->log("Service $service not configured");

            return;
        }

        $transaction = $event->getTransaction();
        $transaction = $transaction->withAddedHeader(self::HEADER_NAME, $testId);
        $this->log("Service $service test header added");

        if (\in_array($service, $this->servicesToMock)) {
            $path = "$this->mockServerUrl/$service";
            $uri = (new Uri($path))->withQuery($transaction->getUri()->getQuery());

            $transaction = $transaction->withUri($uri);
            $this->log("Service $service url mocked to $path");
        }

        $event->setTransaction($transaction);
    }

    public static function getSubscribedEvents()
    {
        return [
            GuzzleEvents::PRE_TRANSACTION => 'onPreTransaction',
        ];
    }

    private function log(string $text)
    {
        $this->logInfo("[test_subscriber] $text");
    }
}
