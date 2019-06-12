<?php

namespace App\Amqp\Consumer;

use App\Application\UseCase\HttpInvoiceUpload\HttpInvoiceUploadRequest;
use App\Application\UseCase\HttpInvoiceUpload\HttpInvoiceUploadUseCase;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class HttpInvoiceUploadConsumer implements ConsumerInterface, LoggingInterface
{
    use LoggingTrait;

    private $useCase;

    public function __construct(HttpInvoiceUploadUseCase $useCase)
    {
        $this->useCase = $useCase;
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
        } catch (\Throwable $exception) {
            $this->logSuppressedException($exception, 'HTTP Invoice Upload Failed', $data);
        }
    }
}
