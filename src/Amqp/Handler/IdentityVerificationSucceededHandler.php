<?php

declare(strict_types=1);

namespace App\Amqp\Handler;

use App\Application\Exception\WorkflowException;
use App\DomainModel\IdentityVerification\IdentityVerificationSucceeder;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Ozean12\Transfer\Message\Identity\IdentityVerificationSucceeded;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class IdentityVerificationSucceededHandler implements MessageHandlerInterface, LoggingInterface
{
    use LoggingTrait;

    private $identityVerificationSucceeder;

    public function __construct(IdentityVerificationSucceeder $identityVerificationSucceeder)
    {
        $this->identityVerificationSucceeder = $identityVerificationSucceeder;
    }

    public function __invoke(IdentityVerificationSucceeded $message): void
    {
        try {
            $this->identityVerificationSucceeder->succeedIdentifcationVerification($message->getCaseUuid());
        } catch (WorkflowException $exception) {
            $this->logInfo('Identity verification step already transitioned', [
                LoggingInterface::KEY_UUID => $message->getCaseUuid(),
            ]);
        } catch (\Exception $exception) {
            $this->logSuppressedException($exception, $exception->getMessage());
        }
    }
}
