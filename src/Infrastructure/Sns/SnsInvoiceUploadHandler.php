<?php

namespace App\Infrastructure\Sns;

use App\DomainModel\MerchantSettings\MerchantSettingsEntity;
use App\DomainModel\MerchantSettings\MerchantSettingsRepositoryInterface;
use App\DomainModel\Order\OrderEntity;
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

    public function handleInvoice(OrderEntity $order, string $event): void
    {
        $this->logInfo('Sending message to SNS');

        $orderUuid = $order->getUuid();

        try {
            $this->snsClient->publish([
                "TopicArn" => $this->topicArn,
                "Message" => "Invoice Received for order #$orderUuid",
                "Subject" => "Invoice Received for order #$orderUuid",
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
                    "invoiceNumber" => [
                        "DataType" => "String",
                        "StringValue" => $order->getInvoiceNumber(),
                    ],
                    "invoiceUrl" => [
                        "DataType" => "String",
                        "StringValue" => $order->getInvoiceUrl(),
                    ],
                    "eventType" => [
                        "DataType" => "String",
                        "StringValue" => $event,
                    ],
                ],
            ]);
        } catch (SnsException $ex) {
            $this->logError("Failed to publish invoice receive SNS topic for order #{order_id} with error: {error}", [
                'order_id' => $orderUuid,
                'error' => $ex->getAwsErrorMessage(),
            ]);
        }
    }
}
