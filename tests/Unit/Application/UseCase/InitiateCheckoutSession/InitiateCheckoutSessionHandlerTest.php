<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\UseCase\InitiateCheckoutSession;

use App\Application\UseCase\InitiateCheckoutSession\InitiateCheckoutSession;
use App\Application\UseCase\InitiateCheckoutSession\InitiateCheckoutSessionHandler;
use App\DomainModel\CheckoutSession\CheckoutSession;
use App\DomainModel\CheckoutSession\CheckoutSessionRepository;
use App\DomainModel\CheckoutSession\Country;
use App\DomainModel\CheckoutSession\Token;
use App\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;

class InitiateCheckoutSessionHandlerTest extends UnitTestCase
{
    private InitiateCheckoutSessionHandler $handler;

    /** @var MockObject */
    private CheckoutSessionRepository $repo;

    protected function setUp(): void
    {
        $this->handler = new InitiateCheckoutSessionHandler(
            $this->repo = $this->createMock(CheckoutSessionRepository::class)
        );
    }

    /**
     * @test
     */
    public function persistNewCheckoutSession(): void
    {
        $command = new InitiateCheckoutSession('t', 'DE', 1);

        $expected = new CheckoutSession(
            Token::fromHash('e3b98a4da31a127d4bde6e43033f66ba274c'),
            new Country('DE'),
            1
        );
        $this->repo
            ->expects($this->once())
            ->method('save')
            ->with($expected);

        $this->handler->execute($command);
    }
}
