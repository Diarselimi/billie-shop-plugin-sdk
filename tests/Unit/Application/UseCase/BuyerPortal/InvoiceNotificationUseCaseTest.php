<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\UseCase\BuyerPortal;

use App\Application\UseCase\BuyerPortal\InvoiceNotification\InvoiceNotificationUseCase;
use App\DomainModel\DebtorCompany\Company;
use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Person\PersonEntity;
use App\Tests\Unit\UnitTestCase;
use Ozean12\Money\Money;
use Ozean12\Smaug\Client\Dto\ResourceToken;
use Ozean12\Smaug\Client\Exception\AccessDeniedHttpException;
use Ozean12\Smaug\Client\SmaugClientInterface;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class InvoiceNotificationUseCaseTest extends UnitTestCase
{
    /**
     * @var SmaugClientInterface|ObjectProphecy
     */
    private $resourceTokenService;

    /**
     * @var MessageBusInterface|ObjectProphecy
     */
    private $messageBus;

    private InvoiceNotificationUseCase $useCase;

    public function setUp(): void
    {
        $this->resourceTokenService = $this->prophesize(SmaugClientInterface::class);
        $this->messageBus = $this->prophesize(MessageBusInterface::class);

        $this->useCase = new InvoiceNotificationUseCase(
            $this->resourceTokenService->reveal(),
            $this->messageBus->reveal()
        );

        $this->useCase->setLogger(new NullLogger());
    }

    /**
     * @test
     */
    public function shouldNotSendMessageIfTokenIsRevoked()
    {
        $orderContainer = $this->prophesize(OrderContainer::class);
        $orderContainer->getDebtorPerson()->willReturn(
            (new PersonEntity())->setEmail('test@ozean12.com')
        );
        $orderContainer->getDebtorCompany()->willReturn(
            (new Company())->setUuid('2d5b9404-b7c7-42ca-8776-0310fbeaa5a8')
        );

        $this->resourceTokenService
            ->createResourceTokenIfNotFound(Argument::cetera())
            ->shouldBeCalledOnce()
            ->willThrow(AccessDeniedHttpException::class);

        $this->messageBus->dispatch(Argument::cetera())->shouldNotBeCalled();
        $this->useCase->execute($orderContainer->reveal(), new Invoice());
    }

    /**
     * @test
     */
    public function shouldSendMessageIfTokenIsFound()
    {
        $debtorPerson = (new PersonEntity())
            ->setFirstName('John')
            ->setLastName('Smith')
            ->setGender('m')
            ->setEmail('test@ozean12.com');

        $debtorCompany = (new Company())
            ->setName('Test Debtor')
            ->setUuid('2d5b9404-b7c7-42ca-8776-0310fbeaa5a8');

        $merchant = (new MerchantEntity())
            ->setName('Test Merchant');

        $resourceToken = (new ResourceToken('ZzwvFM4oOAz3Iuef', new \DateTimeImmutable()))
            ->setEmail($debtorPerson->getEmail())
            ->setResourceType(ResourceToken::RESOURCE_TYPE_BUYER_PORTAL_AP);

        $invoice = (new Invoice())
            ->setUuid('cc6efb86-69e9-444a-9707-ea3122d10810')
            ->setOutstandingAmount(new Money(12.53));

        $orderContainer = $this->prophesize(OrderContainer::class);
        $orderContainer->getDebtorPerson()->willReturn($debtorPerson);
        $orderContainer->getDebtorCompany()->willReturn($debtorCompany);
        $orderContainer->getMerchant()->willReturn($merchant);

        $this->resourceTokenService
            ->createResourceTokenIfNotFound(Argument::cetera())
            ->shouldBeCalledOnce()
            ->willReturn($resourceToken);

        $this->messageBus
            ->dispatch(Argument::cetera())
            ->shouldBeCalled()
            ->willReturn(new Envelope($resourceToken));
        $this->useCase->execute($orderContainer->reveal(), $invoice);
    }
}
