<?php

declare(strict_types=1);

namespace App\Amqp\Handler;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\WorkflowException;
use App\Application\UseCase\MarkOrderAsComplete\MarkOrderAsCompleteRequest;
use App\Application\UseCase\MarkOrderAsComplete\MarkOrderAsCompleteUseCase;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Ozean12\Transfer\Message\Invoice\InvoiceCompleted;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class InvoiceCompletedHandler implements MessageHandlerInterface, LoggingInterface
{
    use LoggingTrait;

    private MarkOrderAsCompleteUseCase $markOrderAsCompleteUseCase;

    public function __construct(MarkOrderAsCompleteUseCase $markOrderAsCompleteUseCase)
    {
        $this->markOrderAsCompleteUseCase = $markOrderAsCompleteUseCase;
    }

    public function __invoke(InvoiceCompleted $invoiceCompleted)
    {
        try {
            $this->markOrderAsCompleteUseCase->execute(
                new MarkOrderAsCompleteRequest($invoiceCompleted->getUuid())
            );
        } catch (OrderNotFoundException | WorkflowException $exception) {
            $this->logSuppressedException($exception, $exception->getMessage());
        }
    }
}
