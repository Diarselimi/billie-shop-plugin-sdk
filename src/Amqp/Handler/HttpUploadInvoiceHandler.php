<?php

namespace App\Amqp\Handler;

use App\Application\UseCase\HttpInvoiceUpload\HttpInvoiceUploadRequest;
use App\Application\UseCase\HttpInvoiceUpload\HttpInvoiceUploadUseCase;
use App\DomainModel\OrderInvoiceDocument\DomainEvent\HttpUploadInvoiceDomainEvent;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class HttpUploadInvoiceHandler implements MessageHandlerInterface, LoggingInterface
{
    use LoggingTrait;

    private HttpInvoiceUploadUseCase $useCase;

    public function __construct(HttpInvoiceUploadUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function __invoke(HttpUploadInvoiceDomainEvent $message)
    {
        $request = new HttpInvoiceUploadRequest(
            $message->getMerchantId(),
            $message->getOrderExternalCode(),
            $message->getInvoiceUuid() ?? '',
            $message->getInvoiceUrl(),
            $message->getInvoiceNumber(),
            $message->getEventSource() ?? $message->getEvent()
        );

        try {
            $this->useCase->execute($request);
        } catch (\Throwable $exception) {
            $this->logError('Http upload exception', [LoggingInterface::KEY_EXCEPTION => $exception]);
            $this->logSuppressedException(
                $exception,
                'HTTP Invoice Upload Failed',
                $request->toArray(),
            );
        }
    }
}
