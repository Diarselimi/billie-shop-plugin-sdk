<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Google\Protobuf\Internal\Message;
use Ozean12\AmqpPackBundle\Mapping\AmqpMapperInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use App\Amqp\Handler\CompanyInformationChangeRequestDecisionIssuedHandler;
use App\Amqp\Handler\IdentityVerificationSucceededHandler;
use Ozean12\Transfer\Message\CompanyInformationChangeRequest\CompanyInformationChangeRequestDecisionIssued;
use Ozean12\Transfer\Message\Identity\IdentityVerificationSucceeded;

class MessengerContext implements Context
{
    protected $kernel;

    private $amqpMapper;

    private $changeRequestDecisionIssuesHandler;

    private $identityVerificationSucceededHandler;

    public function __construct(
        KernelInterface $kernel,
        AmqpMapperInterface $amqpMapper,
        CompanyInformationChangeRequestDecisionIssuedHandler $changeRequestDecisionIssuesHandler,
        IdentityVerificationSucceededHandler $identityVerificationSucceededHandler
    ) {
        $this->kernel = $kernel;
        $this->amqpMapper = $amqpMapper;
        $this->changeRequestDecisionIssuesHandler = $changeRequestDecisionIssuesHandler;
        $this->identityVerificationSucceededHandler = $identityVerificationSucceededHandler;
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
}
