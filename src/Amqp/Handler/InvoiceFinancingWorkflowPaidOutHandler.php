<?php

namespace App\Amqp\Handler;

use App\Application\Exception\WorkflowException;
use App\Application\UseCase\MarkOrderAsPaidOutV1\MarkOrderAsPaidOutV1Request;
use App\Application\UseCase\MarkOrderAsPaidOutV1\MarkOrderAsPaidOutV1UseCase;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Ozean12\Transfer\Message\Invoice\InvoiceFinancingWorkflowPaidOut;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class InvoiceFinancingWorkflowPaidOutHandler implements MessageHandlerInterface, LoggingInterface
{
    use LoggingTrait;

    private MarkOrderAsPaidOutV1UseCase $orderPayoutUseCase;

    public function __construct(MarkOrderAsPaidOutV1UseCase $orderPayoutUseCase)
    {
        $this->orderPayoutUseCase = $orderPayoutUseCase;
    }

    public function __invoke(InvoiceFinancingWorkflowPaidOut $message): void
    {
        try {
            $this->orderPayoutUseCase->execute(
                new MarkOrderAsPaidOutV1Request($message->getInvoice()->getUuid())
            );
        } catch (WorkflowException $exception) {
            $this->logInfo('Order can\'t be marked as financing paid out', [
                LoggingInterface::KEY_UUID => $message->getInvoice()->getPaymentUuid(),
            ]);
        } catch (\Exception $exception) {
            $this->logSuppressedException($exception, $exception->getMessage());
        }
    }
}
