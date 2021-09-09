<?php

namespace App\Amqp\Handler;

use App\Application\Exception\WorkflowException;
use App\Application\UseCase\DeclineOrder\DeclineOrderRequest;
use App\Application\UseCase\DeclineOrder\DeclineOrderUseCase;
use App\DomainModel\Order\DomainEvent\AbstractOrderStateDomainEvent;
use App\DomainModel\Order\OrderRepositoryInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

abstract class AbstractOrderDeclineByStateHandler implements LoggingInterface
{
    use LoggingTrait;

    private $useCase;

    private $orderRepository;

    public function __construct(DeclineOrderUseCase $useCase, OrderRepositoryInterface $orderRepository)
    {
        $this->useCase = $useCase;
        $this->orderRepository = $orderRepository;
    }

    protected function execute(AbstractOrderStateDomainEvent $message, array $targetedStates)
    {
        $orderUuid = $message->getOrderId();

        try {
            $order = $this->orderRepository->getOneByUuid($orderUuid);
            if (!$order || !in_array($order->getState(), $targetedStates, true)) {
                // Order does not exist or it is not in that state anymore
                return;
            }
            $request = new DeclineOrderRequest($orderUuid);
            $this->useCase->execute($request);
        } catch (WorkflowException $exception) {
            return; // order was already manually approved/declined
        } catch (\Exception $exception) {
            $this->logSuppressedException(
                $exception,
                "Failed to move the order to declined because of {reason}",
                ['reason' => $exception->getMessage()]
            );
        }
    }
}
