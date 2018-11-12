<?php

namespace App\Infrastructure\Sns;

use App\DomainModel\Monitoring\LoggingInterface;
use App\DomainModel\Monitoring\LoggingTrait;
use Aws\Sns\Exception\SnsException;
use Aws\Sns\SnsClient;

class InvoiceDownloadEventPublisher implements LoggingInterface, InvoiceDownloadEventPublisherInterface
{
    use LoggingTrait;

    private const SNS_EVENT_NAME = 'order.invoice_download_triggered';

    private const SNS_URL_TEMPLATE = '/Billie_Invoice_%s.pdf';

    private $snsClient;

    private $snsTopicArn;

    public function __construct(SnsClient $snsClient, string $snsTopicArn)
    {
        $this->snsClient = $snsClient;
        $this->snsTopicArn = $snsTopicArn;
    }

    public function publish(int $orderId, int $merchantId, string $invoiceNumber): bool
    {
        $event = $this->buildEvent($orderId, $merchantId, $invoiceNumber);

        try {
            $this->snsClient->publish($event);

            return true;
        } catch (SnsException $ex) {
            $this->logError(
                'Failed to publish ' . self::SNS_EVENT_NAME .
                " SNS event for order #{$orderId}. Error message was: " .
                $ex->getAwsErrorMessage()
            );
        }

        return false;
    }

    private function buildEvent(int $orderId, int $merchantId, string $invoiceNumber): array
    {
        return [
            "TopicArn" => $this->snsTopicArn,
            "Message" => "Invoice Transfer for order #{$orderId}",
            "Subject" => "Invoice Transfer for order #{$orderId}",
            "MessageStructure" => "string",
            "MessageAttributes" => [
                "orderExternalCode" => [
                    "DataType" => "String",
                    "StringValue" => $orderId,
                ],
                "merchantId" => [
                    "DataType" => "Number",
                    "StringValue" => $merchantId,
                ],
                "invoiceNumber" => [
                    "DataType" => "String",
                    "StringValue" => $invoiceNumber,
                ],
                "invoiceUrl" => [
                    "DataType" => "String",
                    "StringValue" => sprintf(self::SNS_URL_TEMPLATE, $invoiceNumber),
                ],
                "eventType" => [
                    "DataType" => "String",
                    "StringValue" => self::SNS_EVENT_NAME,
                ],
            ],
        ];
    }
}
