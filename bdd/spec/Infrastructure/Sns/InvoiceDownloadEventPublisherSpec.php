<?php

namespace spec\App\Infrastructure\Sns;

use App\Infrastructure\Sns\InvoiceDownloadEventPublisher;
use Aws\Sns\Exception\SnsException;
use Aws\Sns\SnsClient;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class InvoiceDownloadEventPublisherSpec extends ObjectBehavior
{
    private const ORDER_ID = 123;

    private const MERCHANT_ID = 2001;

    private const INVOICE_NUMBER = 'DE124087293182842194-1';

    private const SNS_ARN = 'test_arn';

    private const EVENT_NAME = 'order.invoice_download_triggered';

    public function it_is_initializable()
    {
        $this->shouldHaveType(InvoiceDownloadEventPublisher::class);
    }

    public function let(SnsClient $snsClient)
    {
        $this->beConstructedWith($snsClient, self::SNS_ARN);
    }

    public function it_should_publish_event_with_expected_payload(SnsClient $snsClient)
    {
        $event = $this->getExpectedEventPayload();
        $snsClient->publish($event)->shouldBeCalledOnce();
        $this->publish(self::ORDER_ID, self::MERCHANT_ID, self::INVOICE_NUMBER)
            ->shouldReturn(true);
    }

    public function it_should_publish_event_with_expected_path_in_payload(SnsClient $snsClient)
    {
        $path = '/foo/';
        $event = $this->getExpectedEventPayload($path);
        $snsClient->publish($event)->shouldBeCalledOnce();
        $this->publish(self::ORDER_ID, self::MERCHANT_ID, self::INVOICE_NUMBER, $path)
            ->shouldReturn(true);
    }

    public function it_should_log_error_if_SnsException_is_thrown(
        LoggerInterface $logger,
        SnsClient $snsClient
    ) {
        $logger->error(Argument::containingString(self::EVENT_NAME), [])->shouldBeCalledOnce();
        $this->setLogger($logger);
        $event = $this->getExpectedEventPayload();
        $snsClient->publish($event)->willThrow(SnsException::class);
        $this->publish(self::ORDER_ID, self::MERCHANT_ID, self::INVOICE_NUMBER)
            ->shouldReturn(false);
    }

    private function getExpectedEventPayload(string $basePath = '/'): array
    {
        return [
            "TopicArn" => self::SNS_ARN,
            "Message" => "Invoice Transfer for order #" . self::ORDER_ID,
            "Subject" => "Invoice Transfer for order #" . self::ORDER_ID,
            "MessageStructure" => "string",
            "MessageAttributes" => [
                "orderExternalCode" => [
                    "DataType" => "String",
                    "StringValue" => self::ORDER_ID,
                ],
                "merchantId" => [
                    "DataType" => "Number",
                    "StringValue" => self::MERCHANT_ID,
                ],
                "invoiceNumber" => [
                    "DataType" => "String",
                    "StringValue" => self::INVOICE_NUMBER,
                ],
                "invoiceUrl" => [
                    "DataType" => "String",
                    "StringValue" => sprintf('%sBillie_Invoice_%s.pdf', $basePath, self::INVOICE_NUMBER),
                ],
                "eventType" => [
                    "DataType" => "String",
                    "StringValue" => self::EVENT_NAME,
                ],
            ],
        ];
    }
}
