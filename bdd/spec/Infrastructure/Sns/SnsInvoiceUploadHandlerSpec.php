<?php

namespace spec\App\Infrastructure\Sns;

use App\DomainModel\MerchantSettings\MerchantSettingsEntity;
use App\DomainModel\MerchantSettings\MerchantSettingsRepositoryInterface;
use App\Infrastructure\Sns\SnsInvoiceUploadHandler;
use Aws\Sns\Exception\SnsException;
use Aws\Sns\SnsClient;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class SnsInvoiceUploadHandlerSpec extends ObjectBehavior
{
    private const ORDER_EXTERNAL_CODE = "testCode";

    private const PATH = '/foo/';

    private const MERCHANT_ID = 2001;

    private const INVOICE_NUMBER = 'DE124087293182842194-1';

    private const SNS_ARN = 'test_arn';

    private const EVENT_NAME = 'test_event';

    public function it_is_initializable()
    {
        $this->shouldHaveType(SnsInvoiceUploadHandler::class);
    }

    public function let(SnsClient $snsClient, MerchantSettingsRepositoryInterface $merchantSettingsRepository, LoggerInterface $logger)
    {
        $this->beConstructedWith($snsClient, $merchantSettingsRepository, self::SNS_ARN);

        $this->setLogger($logger);
    }

    public function it_supports_merchant(
        MerchantSettingsRepositoryInterface $merchantSettingsRepository,
        MerchantSettingsEntity $settings
    ) {
        $settings->getInvoiceHandlingStrategy()->shouldBeCalledOnce()->willReturn('ftp');
        $merchantSettingsRepository->getOneByMerchant(self::MERCHANT_ID)->shouldBeCalledOnce()->willReturn($settings);

        $this->supports(self::ORDER_EXTERNAL_CODE, self::MERCHANT_ID, self::INVOICE_NUMBER, self::PATH);
    }

    public function it_should_publish_event_with_expected_path_in_payload(SnsClient $snsClient)
    {
        $event = $this->getExpectedEventPayload();

        $snsClient->publish($event)->shouldBeCalledOnce();
        $this->handleInvoice(self::ORDER_EXTERNAL_CODE, self::MERCHANT_ID, self::INVOICE_NUMBER, self::PATH, self::EVENT_NAME);
    }

    public function it_should_log_error_if_sns_exception_is_thrown(SnsClient $snsClient, LoggerInterface $logger)
    {
        $event = $this->getExpectedEventPayload();

        $logger->info(Argument::type('string'), Argument::type('array'))->shouldBeCalledOnce();
        $logger->error(Argument::type('string'), Argument::type('array'))->shouldBeCalledOnce();

        $snsClient->publish($event)->willThrow(SnsException::class);
        $this->handleInvoice(self::ORDER_EXTERNAL_CODE, self::MERCHANT_ID, self::INVOICE_NUMBER, self::PATH, self::EVENT_NAME);
    }

    private function getExpectedEventPayload(): array
    {
        return [
            "TopicArn" => self::SNS_ARN,
            "Message" => "Invoice Received for order #" . self::ORDER_EXTERNAL_CODE,
            "Subject" => "Invoice Received for order #" . self::ORDER_EXTERNAL_CODE,
            "MessageStructure" => "string",
            "MessageAttributes" => [
                "orderExternalCode" => [
                    "DataType" => "String",
                    "StringValue" => self::ORDER_EXTERNAL_CODE,
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
