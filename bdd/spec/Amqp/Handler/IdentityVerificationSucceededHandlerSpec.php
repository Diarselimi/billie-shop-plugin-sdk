<?php

declare(strict_types=1);

namespace spec\App\Amqp\Handler;

use App\DomainModel\IdentityVerification\IdentityVerificationSucceeder;
use Billie\MonitoringBundle\Service\Alerting\Sentry\Raven\RavenClient;
use Ozean12\Transfer\Message\Identity\IdentityVerificationSucceeded;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class IdentityVerificationSucceededHandlerSpec extends ObjectBehavior
{
    public function let(
        IdentityVerificationSucceeder $succeeder,
        LoggerInterface $logger,
        RavenClient $sentry
    ) {
        $this->beConstructedWith($succeeder);
        $this->setLogger($logger);
        $this->setSentry($sentry);
    }

    public function it_should_catch_exceptions(
        IdentityVerificationSucceeder $succeeder,
        RavenClient $sentry
    ) {
        $caseUuid = 'dummy-uuid';
        $message = new IdentityVerificationSucceeded();
        $message->setCaseUuid($caseUuid);

        $sentry->captureException(Argument::any())->shouldBeCalled();

        $succeeder
            ->succeedIdentifcationVerification($caseUuid)
            ->shouldBeCalledOnce()
            ->willThrow(\Exception::class);
        $this->__invoke($message);
    }
}
