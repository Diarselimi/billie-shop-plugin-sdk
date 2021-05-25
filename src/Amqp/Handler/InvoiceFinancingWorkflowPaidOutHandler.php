<?php

namespace App\Amqp\Handler;

use App\Application\UseCase\MarkOrderAsPaidOut\MarkOrderAsPaidOutRequest;
use App\Application\UseCase\MarkOrderAsPaidOut\MarkOrderAsPaidOutUseCase;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Ozean12\Transfer\Message\Invoice\InvoiceFinancingWorkflowPaidOut;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class InvoiceFinancingWorkflowPaidOutHandler implements MessageHandlerInterface, LoggingInterface
{
    use LoggingTrait;

    private MarkOrderAsPaidOutUseCase $orderPayoutUseCase;

    public function __construct(MarkOrderAsPaidOutUseCase $orderPayoutUseCase)
    {
        $this->orderPayoutUseCase = $orderPayoutUseCase;
    }

    public function __invoke(InvoiceFinancingWorkflowPaidOut $message): void
    {
        try {
            $this->orderPayoutUseCase->execute(
                new MarkOrderAsPaidOutRequest($message->getInvoice()->getUuid())
            );
        } catch (\Exception $exception) {
            $this->logSuppressedException($exception, $exception->getMessage());
        }
    }
}
