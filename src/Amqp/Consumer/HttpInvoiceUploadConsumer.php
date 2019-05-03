<?php

namespace App\Amqp\Consumer;

use App\Application\UseCase\HttpInvoiceUpload\HttpInvoiceUploadException;
use App\Application\UseCase\HttpInvoiceUpload\HttpInvoiceUploadRequest;
use App\Application\UseCase\HttpInvoiceUpload\HttpInvoiceUploadUseCase;
use Billie\MonitoringBundle\Service\Alerting\Slack\SlackClientAwareInterface;
use Billie\MonitoringBundle\Service\Alerting\Slack\SlackClientAwareTrait;
use Billie\MonitoringBundle\Service\Alerting\Slack\SlackMessageAttachmentField;
use Billie\MonitoringBundle\Service\Alerting\Slack\SlackMessageFactory;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class HttpInvoiceUploadConsumer implements ConsumerInterface, LoggingInterface, SlackClientAwareInterface
{
    use LoggingTrait, SlackClientAwareTrait;

    private $useCase;

    private $slackMessageFactory;

    public function __construct(HttpInvoiceUploadUseCase $useCase, SlackMessageFactory $slackMessageFactory)
    {
        $this->useCase = $useCase;
        $this->slackMessageFactory = $slackMessageFactory;
    }

    public function execute(AMQPMessage $msg)
    {
        $data = $msg->getBody();
        $data = json_decode($data, true);

        $request = new HttpInvoiceUploadRequest(
            $data['merchant_id'],
            $data['order_external_code'],
            $data['invoice_url'],
            $data['invoice_number'],
            $data['event']
        );

        try {
            $this->useCase->execute($request);
        } catch (HttpInvoiceUploadException $exception) {
            $this->logError('Invoice download failed: {error}', [
                'exception' => $exception,
                'error' => $exception->getMessage(),
            ]);

            $this->sendSlackMessage($request, $exception->getMessage());
        } catch (\Exception $exception) {
            $this->logSuppressedException(
                $exception,
                "Failed to upload invoice because of {reason}",
                ['reason' => $exception->getMessage()]
            );
        }
    }

    private function sendSlackMessage(HttpInvoiceUploadRequest $request, string $error): void
    {
        $message = $this->slackMessageFactory->createSimpleWithServiceInfo(
            'Handling invoice failed',
            'Handling invoice failed',
            null,
            new SlackMessageAttachmentField('Merchant Id', $request->getMerchantId(), true),
            new SlackMessageAttachmentField('Invoice Number', $request->getInvoiceNumber(), true),
            new SlackMessageAttachmentField('Event', $request->getEvent(), true),
            new SlackMessageAttachmentField('Error', $error, true)
        );

        $this->getSlackClient()->sendMessage($message);
    }
}
