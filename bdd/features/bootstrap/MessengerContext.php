<?php

namespace App\Tests\Functional\Context;

use App\Amqp\Handler\CompanyInformationChangeRequestDecisionIssuedHandler;
use App\Amqp\Handler\IdentityVerificationSucceededHandler;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Coduo\PHPMatcher\PHPMatcher;
use Google\Protobuf\Internal\Message;
use Ozean12\AmqpPackBundle\Mapping\AmqpMapperInterface;
use Ozean12\Transfer\Message\CompanyInformationChangeRequest\CompanyInformationChangeRequestDecisionIssued;
use Ozean12\Transfer\Message\Identity\IdentityVerificationSucceeded;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Webmozart\Assert\Assert;

class MessengerContext implements Context
{
    protected KernelInterface $kernel;

    private AmqpMapperInterface $amqpMapper;

    private CompanyInformationChangeRequestDecisionIssuedHandler $changeRequestDecisionIssuesHandler;

    private IdentityVerificationSucceededHandler $identityVerificationSucceededHandler;

    private MessageBusInterface $traceableMessageBus;

    public function __construct(
        KernelInterface $kernel,
        AmqpMapperInterface $amqpMapper,
        CompanyInformationChangeRequestDecisionIssuedHandler $changeRequestDecisionIssuesHandler,
        IdentityVerificationSucceededHandler $identityVerificationSucceededHandler,
        MessageBusInterface $traceableMessageBus
    ) {
        $this->kernel = $kernel;
        $this->amqpMapper = $amqpMapper;
        $this->changeRequestDecisionIssuesHandler = $changeRequestDecisionIssuesHandler;
        $this->identityVerificationSucceededHandler = $identityVerificationSucceededHandler;
        $this->traceableMessageBus = $traceableMessageBus;
    }

    /**
     * @When I consume an existing queue message of type :messageName containing this payload:
     */
    public function iConsumeMessage(string $messageName, PyStringNode $messageValues)
    {
        $className = $this->amqpMapper->mapToClassName($messageName);

        /** @var Message $message */
        $message = new $className;
        $message->mergeFromJsonString($messageValues);

        switch ($className) {
            case CompanyInformationChangeRequestDecisionIssued::class:
                $this->changeRequestDecisionIssuesHandler->__invoke($message);

                break;
            case IdentityVerificationSucceeded::class:
                $this->identityVerificationSucceededHandler->__invoke($message);

                break;
        }
    }

    /**
     * @Then queue should contain message with routing key :routingKey
     */
    public function queueDispatchedMessages(string $routingKey): void
    {
        $dispatchedMessages = $this->traceableMessageBus->getDispatchedMessages();

        $dispatchedMessages = array_filter(
            $dispatchedMessages,
            function (array $messageContext) use ($routingKey) {
                return $routingKey === $this->amqpMapper->mapToKey(get_class($messageContext['message']));
            }
        );

        Assert::greaterThan($dispatchedMessages, 0, 'There is no dispatched message with name ' . $routingKey);
    }

    /**
     * @return Message[]
     */
    private function getQueuedMessages(?string $routingKey = null): array
    {
        $dispatchedMessages = $this->traceableMessageBus->getDispatchedMessages();

        if ($routingKey !== null) {
            $dispatchedMessages = array_filter(
                $dispatchedMessages,
                function (array $messageContext) use ($routingKey) {
                    return $routingKey === $this->amqpMapper->mapToKey(get_class($messageContext['message']));
                }
            );
        }

        return $dispatchedMessages;
    }

    /**
     * @Then queue should contain message with routing key :routingKey with below data:
     */
    public function queueDispatchedMessagesContains(string $routingKey, PyStringNode $string): void
    {
        $dispatchedMessages = $this->getQueuedMessages($routingKey);

        Assert::greaterThan(count($dispatchedMessages), 0, 'There is no dispatched message with name ' . $routingKey);

        $className = $this->amqpMapper->mapToClassName($routingKey);

        /** @var Message $expectedMessage */
        $expectedMessage = new $className;
        $expectedMessage->mergeFromJsonString((string) $string);

        $error = null;
        $atLeastOneMatched = false;
        $matcher = new PHPMatcher();
        foreach ($dispatchedMessages as $dispatchedMessage) {
            /** @var Message $dispatchedMessage */
            $dispatchedMessage = $dispatchedMessage['message'];

            if (!$matcher->match(
                $dispatchedMessage->serializeToJsonString(),
                $expectedMessage->serializeToJsonString()
            )) {
                $error = (string) $matcher->error();
            } else {
                $atLeastOneMatched = true;
            }
        }

        if (null !== $error && !$atLeastOneMatched) {
            throw new \Exception($error);
        }
    }

    /**
     * @Then print queued messages
     */
    public function printQueuedMessages(): void
    {
        $messages = $this->getQueuedMessages();
        foreach ($messages as $message) {
            /** @var Message $dispatchedMessage */
            $dispatchedMessage = $message['message'];
            $routingKey = $this->amqpMapper->mapToKey(get_class($dispatchedMessage));
            print_r(
                ['routing_key' => $routingKey, 'payload' => $dispatchedMessage->serializeToJsonString()]
            );
        }
    }
}
