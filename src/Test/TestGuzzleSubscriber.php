<?php

namespace App\Test;

use App\DomainModel\Monitoring\LoggingInterface;
use App\DomainModel\Monitoring\LoggingTrait;
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

    public function __construct(
        RequestStack $request,
        string $mockServerUrl,
        array $servicesToMock,
        array $servicesToProxyHeader
    ) {
        $this->request = $request->getCurrentRequest();
        $this->mockServerUrl = $mockServerUrl;
        $this->servicesToMock = $servicesToMock;
        $this->servicesToProxyHeader = $servicesToProxyHeader;
    }

    public function onPreTransaction(PreTransactionEvent $event)
    {
        $testHeader = $this->request->headers->get(self::HEADER_NAME);
        if (!$testHeader) {
            return;
        }

        $service = $event->getServiceName();
        $this->log("Service $service check");
        if (!\in_array($service, $this->servicesToMock) && !\in_array($service, $this->servicesToProxyHeader)) {
            $this->log("Service $service not configured");

            return;
        }

        $transaction = $event->getTransaction();
        $transaction = $transaction->withAddedHeader(self::HEADER_NAME, $testHeader);
        $this->log("Service $service test header added");

        if (\in_array($service, $this->servicesToMock)) {
            $url = "$this->mockServerUrl/$service";
            $transaction = $transaction->withUri(new Uri($url));
            $this->log("Service $service url mocked to $url");
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
