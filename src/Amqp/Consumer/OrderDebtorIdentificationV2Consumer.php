<?php

namespace App\Amqp\Consumer;

use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\IdentifyAndScoreDebtor\Exception\DebtorNotIdentifiedException;
use App\Application\UseCase\OrderDebtorIdentificationV2\OrderDebtorIdentificationV2Request;
use App\Application\UseCase\OrderDebtorIdentificationV2\OrderDebtorIdentificationV2UseCase;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class OrderDebtorIdentificationV2Consumer implements ConsumerInterface, LoggingInterface
{
    use LoggingTrait;

    private $useCase;

    public function __construct(OrderDebtorIdentificationV2UseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(AMQPMessage $msg)
    {
        $data = $msg->getBody();
        $data = json_decode($data, true);

        $request = new OrderDebtorIdentificationV2Request($data['order_id'], null, $data['v1_company_id']);

        try {
            $this->useCase->execute($request);
        } catch (OrderNotFoundException $exception) {
            $this->logSuppressedException($exception, 'Order not found');
        } catch (DebtorNotIdentifiedException $exception) {
        }
    }
}
