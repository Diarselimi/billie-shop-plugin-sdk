<?php

namespace App\Amqp\Handler;

use App\Application\Exception\WorkflowException;
use App\Application\UseCase\MarkOrderAsLate\MarkOrderAsLateRequest;
use App\Application\UseCase\MarkOrderAsLate\MarkOrderAsLateUseCase;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Ozean12\Transfer\Message\Invoice\InvoiceMarkedAsLate;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class InvoiceMarkedAsLateHandler implements MessageHandlerInterface, LoggingInterface
{
    use LoggingTrait;

    private MarkOrderAsLateUseCase $orderMakeLateUseCase;

    public function __construct(MarkOrderAsLateUseCase $orderMakeLateUseCase)
    {
        $this->orderMakeLateUseCase = $orderMakeLateUseCase;
    }

    public function __invoke(InvoiceMarkedAsLate $message): void
    {
        try {
            $this->orderMakeLateUseCase->execute(
                new MarkOrderAsLateRequest($message->getInvoice()->getUuid())
            );
        } catch (WorkflowException $exception) {
            $this->logInfo('Order can\'t be marked as late', [
                LoggingInterface::KEY_UUID => $message->getInvoice()->getPaymentUuid(),
            ]);
        } catch (\Exception $exception) {
            $this->logSuppressedException($exception, $exception->getMessage());
        }
    }
}
