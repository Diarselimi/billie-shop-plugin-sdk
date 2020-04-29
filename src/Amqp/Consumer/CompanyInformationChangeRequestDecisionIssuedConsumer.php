<?php

namespace App\Amqp\Consumer;

use App\Amqp\Handler\CompanyInformationChangeRequestDecisionIssuedHandler;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Ozean12\Transfer\Message\CompanyInformationChangeRequest\CompanyInformationChangeRequestDecisionIssued as ProtoMessage;

/**
 * Note: this is a temporary solution until Salesforce sends binary proto messages.
 * A ProtoMessage is created here to prepare for the near future.
 */
class CompanyInformationChangeRequestDecisionIssuedConsumer implements ConsumerInterface, LoggingInterface
{
    use LoggingTrait;

    private $handler;

    public function __construct(CompanyInformationChangeRequestDecisionIssuedHandler $handler)
    {
        $this->handler = $handler;
    }

    public function execute(AMQPMessage $msg)
    {
        $data = $msg->getBody();
        $data = json_decode($data, true);

        if (!isset($data['request_uuid']) || !isset($data['decision'])) {
            $this->logInfo('Cannot consume change request decision because the mandatory keys are missing');

            return;
        }

        $protoMessage = new ProtoMessage($data);
        $this->handler->__invoke($protoMessage);
    }
}
