<?php

namespace spec\App\Infrastructure\OrderInvoice;

use App\DomainModel\MerchantSettings\MerchantSettingsEntity;
use App\DomainModel\MerchantSettings\MerchantSettingsRepositoryInterface;
use App\DomainModel\Order\OrderEntity;
use App\Infrastructure\OrderInvoice\SnsInvoiceDocumentUploadHandler;
use Aws\Sns\Exception\SnsException;
use Aws\Sns\SnsClient;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class SnsInvoiceDocumentUploadHandlerSpec extends ObjectBehavior
{
    private const ORDER_UUID = 'test-order-uuid';

    private const ORDER_EXTERNAL_CODE = "testCode";

    private const PATH = '/foo/';

    private const MERCHANT_ID = 2001;

    private const INVOICE_NUMBER = 'DE124087293182842194-1';

    private const SNS_ARN = 'test_arn';

    private const EVENT_NAME = 'test_event';

    public function it_is_initializable()
    {
        $this->shouldHaveType(SnsInvoiceDocumentUploadHandler::class);
    }

    public function let(
        SnsClient $snsClient,
        MerchantSettingsRepositoryInterface $merchantSettingsRepository,
        LoggerInterface $logger,
        OrderEntity $order
    ) {
        $order->getUuid()->willReturn(self::ORDER_UUID);
        $order->getMerchantId()->willReturn(self::MERCHANT_ID);
        $order->getExternalCode()->willReturn(self::ORDER_EXTERNAL_CODE);
        $order->getInvoiceNumber()->willReturn(self::INVOICE_NUMBER);
        $order->getInvoiceUrl()->willReturn(self::PATH);

        $this->beConstructedWith($snsClient, $merchantSettingsRepository, self::SNS_ARN);

        $this->setLogger($logger);
    }

    public function it_supports_merchant(
        MerchantSettingsRepositoryInterface $merchantSettingsRepository,
        MerchantSettingsEntity $settings
    ) {
        $settings->getInvoiceHandlingStrategy()->shouldBeCalledOnce()->willReturn('ftp');
        $merchantSettingsRepository->getOneByMerchant(self::MERCHANT_ID)->shouldBeCalledOnce()->willReturn($settings);

        $this->supports(self::MERCHANT_ID);
    }

    public function it_should_publish_event_with_expected_path_in_payload(SnsClient $snsClient, OrderEntity $order)
    {
        $snsClient->publish(Argument::any())->shouldBeCalledOnce();

        $this->handle($order, 'test', self::PATH, self::INVOICE_NUMBER, self::EVENT_NAME);
    }

    public function it_should_log_error_if_sns_exception_is_thrown(
        SnsClient $snsClient,
        LoggerInterface $logger,
        OrderEntity $order
    ) {
        $logger->info(Argument::type('string'), Argument::type('array'))->shouldBeCalledOnce();
        $logger->error(Argument::type('string'), Argument::type('array'))->shouldBeCalledOnce();

        $snsClient->publish(Argument::any())->willThrow(SnsException::class);

        $this->handle($order, 'test', self::PATH, self::INVOICE_NUMBER, self::EVENT_NAME);
    }

    private function getExpectedEventPayload(): array
    {
        return [
            "TopicArn" => self::SNS_ARN,
            "Message" => "Invoice Received for order #" . self::ORDER_UUID,
            "Subject" => "Invoice Received for order #" . self::ORDER_UUID,
            "MessageStructure" => "string",
            "MessageAttributes" => [
                "orderUuid" => [
                    "DataType" => "String",
                    "StringValue" => self::ORDER_UUID,
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
                    "StringValue" => self::PATH,
                ],
                "eventType" => [
                    "DataType" => "String",
                    "StringValue" => self::EVENT_NAME,
                ],
            ],
        ];
    }
}
