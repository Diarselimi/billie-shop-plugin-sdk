<?php

namespace App\Tests\Functional\Context;

use App\Amqp\Handler\CompanyInformationChangeRequestDecisionIssuedHandler;
use App\Amqp\Handler\IdentityVerificationSucceededHandler;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
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
     * @Then queue should contain :count messages with routing key :routingKey
     */
    public function queueDispatchedMessageCount(int $count, string $routingKey): void
    {
        $dispatchedMessages = $this->getQueuedMessages($routingKey);

        Assert::eq($count, count($dispatchedMessages));
    }

    /**
     * @Then print queued messages
     */
    public function printQueuedMessages(): void
    {
        $messages = $this->getQueuedMessages();
        if (empty($messages)) {
            echo 'No messages were dispatched' . PHP_EOL;

            return;
        }

        foreach ($messages as $message) {
            /** @var Message $dispatchedMessage */
            $dispatchedMessage = $message['message'];
            $routingKey = $this->amqpMapper->mapToKey(get_class($dispatchedMessage));
            print_r(
                ['routing_key' => $routingKey, 'payload' => $dispatchedMessage->serializeToJsonString()]
            );
        }
    }

    /**
     * @Given /^make sure the order of dispatched messages is as follows:$/
     */
    public function makeSureTheOrderOfDispatchedMessagesIsAsFollows(TableNode $table)
    {
        if (count($table->getRows()) < 2) {
            throw new \Exception('You need to specify at least two messages to check the order.');
        }

        $orderOfMessages = [];
        foreach ($this->getQueuedMessages() as $message) {
            /** @var Message $dispatchedMessage */
            $dispatchedMessage = $message['message'];
            $orderOfMessages[] = $this->amqpMapper->mapToKey(get_class($dispatchedMessage));
        }

        foreach ($table->getRows() as $key => $row) {
            if ($orderOfMessages[$key] !== $row[0]) {
                $error = sprintf(
                    'Expected message %s but got %s, order seems to not be correct.',
                    $row[0],
                    $orderOfMessages[$key]
                );

                throw new \Exception($error);
            }
        }
    }
}
