<?php

namespace App\Infrastructure\OrderInvoice;

use App\DomainModel\MerchantSettings\MerchantSettingsEntity;
use App\DomainModel\MerchantSettings\MerchantSettingsRepositoryInterface;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\OrderInvoiceDocument\UploadHandler\AbstractInvoiceDocumentUploadHandler;
use Aws\Sns\Exception\SnsException;
use Aws\Sns\SnsClient;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class SnsInvoiceDocumentUploadHandler extends AbstractInvoiceDocumentUploadHandler implements LoggingInterface
{
    use LoggingTrait;

    protected const SUPPORTED_STRATEGY = MerchantSettingsEntity::INVOICE_HANDLING_STRATEGY_FTP;

    private SnsClient $snsClient;

    private string $topicArn;

    public function __construct(
        SnsClient $snsClient,
        MerchantSettingsRepositoryInterface $merchantSettingsRepository,
        string $snsInvoiceUploadTopicArn
    ) {
        $this->snsClient = $snsClient;
        $this->topicArn = $snsInvoiceUploadTopicArn;

        parent::__construct($merchantSettingsRepository);
    }

    public function handle(
        OrderEntity $order,
        string $invoiceUuid,
        string $invoiceUrl,
        string $invoiceNumber,
        string $eventSource
    ): void {
        $this->logInfo('Sending message to SNS');

        $orderUuid = $order->getUuid();

        try {
            $this->snsClient->publish([
                "TopicArn" => $this->topicArn,
                "Message" => "Invoice Received for order #{$orderUuid}",
                "Subject" => "Invoice Received for order #{$orderUuid}",
                "MessageStructure" => "string",
                "MessageAttributes" => [
                    "orderUuid" => [
                        "DataType" => "String",
                        "StringValue" => $orderUuid,
                    ],
                    "merchantId" => [
                        "DataType" => "Number",
                        "StringValue" => $order->getMerchantId(),
                    ],
                    "invoiceUuid" => [
                        "DataType" => "String",
                        "StringValue" => $invoiceUuid,
                    ],
                    "invoiceNumber" => [
                        "DataType" => "String",
                        "StringValue" => $invoiceNumber,
                    ],
                    "invoiceUrl" => [
                        "DataType" => "String",
                        "StringValue" => $invoiceUrl,
                    ],
                    "eventType" => [
                        "DataType" => "String",
                        "StringValue" => $eventSource,
                    ],
                ],
            ]);
        } catch (SnsException $ex) {
            $this->logError("Failed to publish invoice receive SNS topic for order #{uuid} with error: {reason}", [
                LoggingInterface::KEY_UUID => $orderUuid,
                LoggingInterface::KEY_REASON => $ex->getAwsErrorMessage(),
            ]);
        }
    }
}
