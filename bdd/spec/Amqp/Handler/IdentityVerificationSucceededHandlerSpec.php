<?php

declare(strict_types=1);

namespace spec\App\Amqp\Handler;

use App\DomainModel\IdentityVerification\IdentityVerificationSucceeder;
use Billie\MonitoringBundle\Service\Alerting\Sentry\Raven\RavenClient;
use Ozean12\Transfer\Message\Identity\IdentityVerificationSucceeded;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\NullLogger;

class IdentityVerificationSucceededHandlerSpec extends ObjectBehavior
{
    public function let(
        IdentityVerificationSucceeder $identityVerificationSucceeder,
        RavenClient $sentry
    ) {
        $this->beConstructedWith($identityVerificationSucceeder);

        $this->setLogger(new NullLogger())->setSentry($sentry);
    }

    public function it_should_catch_exceptions(
        IdentityVerificationSucceeder $identityVerificationSucceeder,
        RavenClient $sentry
    ) {
        $caseUuid = 'dummy-uuid';
        $message = new IdentityVerificationSucceeded();
        $message->setCaseUuid($caseUuid);

        $sentry->captureException(Argument::any())->shouldBeCalled();

        $identityVerificationSucceeder
            ->succeedIdentifcationVerification($caseUuid)
            ->shouldBeCalledOnce()
            ->willThrow(\Exception::class);

        $this->__invoke($message);
    }

    public function it_ignores_the_message_if_its_for_flow_customer(IdentityVerificationSucceeder $identityVerificationSucceeder)
    {
        $caseUuid = 'dummy-uuid';
        $message = new IdentityVerificationSucceeded();
        $message->setCaseUuid($caseUuid);
        $message->setUserPersonUuid('person-uuid');

        $identityVerificationSucceeder
            ->succeedIdentifcationVerification($caseUuid)
            ->shouldNotBeCalled();

        $this->__invoke($message);
    }
}
