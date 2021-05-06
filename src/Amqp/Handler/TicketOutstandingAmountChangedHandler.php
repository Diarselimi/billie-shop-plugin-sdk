<?php

declare(strict_types=1);

namespace App\Amqp\Handler;

use App\Application\UseCase\OrderOutstandingAmountChange\OrderOutstandingAmountChangeRequest;
use App\Application\UseCase\OrderOutstandingAmountChange\OrderOutstandingAmountChangeUseCase;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Ozean12\Money\Money;
use Ozean12\Transfer\Message\Ticket\TicketOutstandingAmountChanged;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class TicketOutstandingAmountChangedHandler implements MessageHandlerInterface, LoggingInterface
{
    use LoggingTrait;

    private OrderOutstandingAmountChangeUseCase $useCase;

    public function __construct(OrderOutstandingAmountChangeUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function __invoke(TicketOutstandingAmountChanged $message)
    {
        try {
            $request = new OrderOutstandingAmountChangeRequest(
                $message->getPaymentUuid(),
                $message->getType(),
                (new Money($message->getAmountChanged(), 2)),
                (new Money($message->getOutstandingAmount(), 2)),
                (new Money($message->getPaidAmount(), 2)),
                $message->getDebtorIban(),
                $message->getDebtorName()
            );
            $this->useCase->execute($request);
        } catch (\Exception $exception) {
            $this->logSuppressedException($exception, $exception->getMessage());
        }
    }
}
