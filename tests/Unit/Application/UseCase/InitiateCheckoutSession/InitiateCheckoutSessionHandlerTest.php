<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\UseCase\InitiateCheckoutSession;

use App\Application\UseCase\InitiateCheckoutSession\InitiateCheckoutSession;
use App\Application\UseCase\InitiateCheckoutSession\InitiateCheckoutSessionHandler;
use App\Application\UseCase\InitiateCheckoutSession\MerchantNotFound;
use App\DomainModel\CheckoutSession\CheckoutSession;
use App\DomainModel\CheckoutSession\CheckoutSessionRepository;
use App\DomainModel\CheckoutSession\Country;
use App\DomainModel\CheckoutSession\Token;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantRepository;
use App\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;

class InitiateCheckoutSessionHandlerTest extends UnitTestCase
{
    private InitiateCheckoutSessionHandler $handler;

    /** @var MockObject */
    private CheckoutSessionRepository $sessionRepo;

    /** @var MockObject */
    private MerchantRepository $merchantRepo;

    protected function setUp(): void
    {
        $this->handler = new InitiateCheckoutSessionHandler(
            $this->sessionRepo = $this->createMock(CheckoutSessionRepository::class),
            $this->merchantRepo = $this->createMock(MerchantRepository::class)
        );
    }

    /**
     * @test
     */
    public function persistCheckoutSessionForKlarnaIfMerchantIsFound(): void
    {
        $this->merchantRepo
            ->expects($this->once())
            ->method('getByPartnerIdentifier')
            ->willReturn($this->stubMerchantWithId(666));

        $this->sessionRepo
            ->expects($this->once())
            ->method('save')
            ->with(
                new CheckoutSession(
                    Token::fromHash('e3b98a4da31a127d4bde6e43033f66ba274c'),
                    new Country('DE'),
                    666,
                    null
                )
            );

        $this->handler->execute(InitiateCheckoutSession::forKlarna('t', 'DE', 'klarna-merchant-id'));
    }

    /**
     * @test
     */
    public function throwExceptionIfKlarnaMerchantIsNotFound(): void
    {
        $this->merchantRepo
            ->expects($this->once())
            ->method('getByPartnerIdentifier')
            ->willReturn(null);

        $this->sessionRepo
            ->expects($this->never())
            ->method('save');
        $this->expectException(MerchantNotFound::class);
        $this->expectExceptionMessage('Could not found klarna merchant id \'klarna-merchant-id\'');

        $this->handler->execute(InitiateCheckoutSession::forKlarna('t', 'DE', 'klarna-merchant-id'));
    }

    /**
     * @test
     */
    public function persistCheckoutSessionForMerchantIfMerchantIsFound(): void
    {
        $this->merchantRepo
            ->expects($this->once())
            ->method('getOneById')
            ->willReturn($this->stubMerchantWithId(444));

        $this->sessionRepo
            ->expects($this->once())
            ->method('save')
            ->with(
                new CheckoutSession(
                    Token::fromHash('e3b98a4da31a127d4bde6e43033f66ba274c'),
                    new Country('DE'),
                    444,
                    'debtor-id'
                )
            );

        $this->handler->execute(InitiateCheckoutSession::forDirectIntegration('t', 'DE', 444, 'debtor-id'));
    }

    /**
     * @test
     */
    public function throwExceptionIfMerchantIsNotFound(): void
    {
        $this->merchantRepo
            ->expects($this->once())
            ->method('getOneById')
            ->willReturn(null);

        $this->sessionRepo
            ->expects($this->never())
            ->method('save');
        $this->expectException(MerchantNotFound::class);
        $this->expectExceptionMessage('Could not found merchant id \'444\'');

        $this->handler->execute(InitiateCheckoutSession::forDirectIntegration('t', 'DE', 444, 'debtor-id'));
    }

    private function stubMerchantWithId(int $id): MerchantEntity
    {
        $merchant = $this->createStub(MerchantEntity::class);
        $merchant
            ->method('getId')
            ->willReturn($id);

        return $merchant;
    }
}
