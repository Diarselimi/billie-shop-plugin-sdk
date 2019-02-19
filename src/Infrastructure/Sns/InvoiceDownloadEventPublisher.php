<?php

namespace App\Infrastructure\Sns;

use Aws\Sns\Exception\SnsException;
use Aws\Sns\SnsClient;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class InvoiceDownloadEventPublisher implements LoggingInterface, InvoiceDownloadEventPublisherInterface
{
    use LoggingTrait;

    private const SNS_EVENT_NAME = 'order.invoice_download_triggered';

    private const INVOICE_URL_TEMPLATE = '%sBillie_Invoice_%s.pdf';

    private $snsClient;

    private $snsTopicArn;

    public function __construct(SnsClient $snsClient, string $snsTopicArn)
    {
        $this->snsClient = $snsClient;
        $this->snsTopicArn = $snsTopicArn;
    }

    public function publish(string $orderExternalCode, int $merchantId, string $invoiceNumber, string $basePath = '/'): bool
    {
        $event = $this->buildEvent($orderExternalCode, $merchantId, $invoiceNumber, $basePath);

        try {
            $this->snsClient->publish($event);

            return true;
        } catch (SnsException $ex) {
            $this->logError(
                'Failed to publish ' . self::SNS_EVENT_NAME .
                " SNS event for order #{$orderExternalCode}. Error message was: " .
                $ex->getAwsErrorMessage()
            );
        }

        return false;
    }

    private function buildEvent(string $orderExternalCode, int $merchantId, string $invoiceNumber, string $basePath = '/'): array
    {
        return [
            "TopicArn" => $this->snsTopicArn,
            "Message" => "Invoice Transfer for order #{$orderExternalCode}",
            "Subject" => "Invoice Transfer for order #{$orderExternalCode}",
            "MessageStructure" => "string",
            "MessageAttributes" => [
                "orderExternalCode" => [
                    "DataType" => "String",
                    "StringValue" => $orderExternalCode,
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
                    "StringValue" => sprintf(self::INVOICE_URL_TEMPLATE, $basePath, $invoiceNumber),
                ],
                "eventType" => [
                    "DataType" => "String",
                    "StringValue" => self::SNS_EVENT_NAME,
                ],
            ],
        ];
    }
}
