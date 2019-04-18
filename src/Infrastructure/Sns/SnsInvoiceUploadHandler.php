<?php

namespace App\Infrastructure\Sns;

use App\DomainModel\MerchantSettings\MerchantSettingsEntity;
use App\DomainModel\MerchantSettings\MerchantSettingsRepositoryInterface;
use App\DomainModel\OrderInvoice\AbstractSettingsAwareInvoiceUploadHandler;
use Aws\Sns\Exception\SnsException;
use Aws\Sns\SnsClient;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class SnsInvoiceUploadHandler extends AbstractSettingsAwareInvoiceUploadHandler implements LoggingInterface
{
    use LoggingTrait;

    protected const SUPPORTED_STRATEGY = MerchantSettingsEntity::INVOICE_HANDLING_STRATEGY_FTP;

    private $snsClient;

    private $topicArn;

    public function __construct(
        SnsClient $snsClient,
        MerchantSettingsRepositoryInterface $merchantSettingsRepository,
        string $snsInvoiceUploadTopicArn
    ) {
        $this->snsClient = $snsClient;
        $this->topicArn = $snsInvoiceUploadTopicArn;

        parent::__construct($merchantSettingsRepository);
    }

    public function handleInvoice(
        string $orderExternalCode,
        int $merchantId,
        string $invoiceNumber,
        string $invoiceUrl,
        string $event
    ): void {
        $this->logInfo('Sending message to SNS');

        try {
            $this->snsClient->publish([
                "TopicArn" => $this->topicArn,
                "Message" => "Invoice Received for order #$orderExternalCode",
                "Subject" => "Invoice Received for order #$orderExternalCode",
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
                        "StringValue" => $invoiceUrl,
                    ],
                    "eventType" => [
                        "DataType" => "String",
                        "StringValue" => $event,
                    ],
                ],
            ]);
        } catch (SnsException $ex) {
            $this->logError("Failed to publish invoice receive SNS topic for order #{order_id} with error: {error}", [
                'order_id' => $orderExternalCode,
                'error' => $ex->getAwsErrorMessage(),
            ]);
        }
    }
}
