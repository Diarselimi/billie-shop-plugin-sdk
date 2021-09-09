<?php

namespace App\Amqp\Handler;

use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\IdentifyAndScoreDebtor\Exception\DebtorNotIdentifiedException;
use App\Application\UseCase\OrderDebtorIdentificationV2\OrderDebtorIdentificationV2Request;
use App\Application\UseCase\OrderDebtorIdentificationV2\OrderDebtorIdentificationV2UseCase;
use App\DomainModel\Order\DomainEvent\OrderDebtorIdentificationV2DomainEvent;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class OrderDebtorIdentificationV2Handler implements MessageHandlerInterface, LoggingInterface
{
    use LoggingTrait;

    private $useCase;

    public function __construct(OrderDebtorIdentificationV2UseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function __invoke(OrderDebtorIdentificationV2DomainEvent $message)
    {
        $request = new OrderDebtorIdentificationV2Request(
            $message->getOrderId(),
            null,
            $message->getV1CompanyId()
        );

        try {
            $this->useCase->execute($request);
        } catch (OrderNotFoundException $exception) {
            $this->logSuppressedException($exception, 'Order not found');
        } catch (DebtorNotIdentifiedException $exception) {
        }
    }
}
