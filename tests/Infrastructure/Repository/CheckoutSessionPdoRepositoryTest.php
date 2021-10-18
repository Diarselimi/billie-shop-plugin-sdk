<?php

namespace App\Tests\Infrastructure\Repository;

use App\DomainModel\CheckoutSession\CheckoutSession;
use App\DomainModel\CheckoutSession\Country;
use App\DomainModel\CheckoutSession\Token;
use App\Infrastructure\Repository\CheckoutSessionPdoRepository;
use App\Tests\Infrastructure\DatabaseConnectionTrait;
use App\Tests\Infrastructure\ServiceLoaderTrait;
use PHPUnit\Framework\TestCase;

class CheckoutSessionPdoRepositoryTest extends TestCase
{
    use ServiceLoaderTrait;
    use DatabaseConnectionTrait;

    private CheckoutSessionPdoRepository $repository;

    public function setUp(): void
    {
        $this->repository = $this->loadService(CheckoutSessionPdoRepository::class);

        $this->truncateDbTable('checkout_sessions');
        $this->makeRepositoryPdoIgnoreFk($this->repository);
    }

    public function tokensWithExpectedCheckoutSessions(): array
    {
        $active = new CheckoutSession(Token::fromHash('active'), new Country('DE'), 1, 'external_code');
        $notActive = new CheckoutSession(Token::fromHash('not-active'), new Country('DE'), 1, null);
        $notActive->deactivate();

        return [
            'not registered' => ['non-registered', null],
            'registered and active' => ['active', $active],
            'registered and not active' => ['not-active', $notActive],
        ];
    }

    /**
     * @test
     * @dataProvider tokensWithExpectedCheckoutSessions
     */
    public function returnCheckoutSessionByToken(string $token, ?CheckoutSession $expected): void
    {
        $this->insertInDb(
            'checkout_sessions',
            [
                'uuid' => 'active',
                'merchant_id' => 1,
                'merchant_debtor_external_id' => 'external_code',
                'is_active' => '1',
            ],
            [
                'uuid' => 'not-active',
                'merchant_id' => 1,
                'merchant_debtor_external_id' => null,
                'is_active' => '0',
            ],
        );

        $checkoutSession = $this->repository->findByToken(Token::fromHash($token));

        $this->assertCheckoutSessionsAreEqual($expected, $checkoutSession);
    }

    /**
     * @test
     */
    public function saveNewCheckoutSession(): void
    {
        $checkoutSession = new CheckoutSession(Token::fromHash('token'), new Country('DE'), 1, 'external_code');

        $this->repository->save($checkoutSession);

        $this->assertRegisterIsInDbTable('checkout_sessions', [
            'uuid' => 'token',
            'merchant_id' => 1,
            'merchant_debtor_external_id' => 'external_code',
            'is_active' => '1',
        ]);
    }

    /**
     * @test
     */
    public function updateExistingCheckoutSession(): void
    {
        $this->insertInDb('checkout_sessions', [
            'uuid' => 'token',
            'merchant_id' => 1,
            'merchant_debtor_external_id' => 'external_code',
            'is_active' => '0',
        ]);

        $checkoutSession = new CheckoutSession(Token::fromHash('token'), new Country('DE'), 1, 'external_code');
        $this->repository->save($checkoutSession);

        $this->assertRegisterIsInDbTable('checkout_sessions', [
            'uuid' => 'token',
            'merchant_id' => 1,
            'merchant_debtor_external_id' => 'external_code',
            'is_active' => '1',
        ]);
    }

    private function assertCheckoutSessionsAreEqual(?CheckoutSession $expected, ?CheckoutSession $actual): void
    {
        $expected = null === $expected ? null : [
            'token' => $expected->token(),
            'country' => $expected->country(),
            'merchantId' => $expected->merchantId(),
            'externalReference' => $expected->debtorExternalId(),
            'isActive' => $expected->isActive(),
        ];
        $actual = null === $actual ? null : [
            'token' => $actual->token(),
            'country' => $actual->country(),
            'merchantId' => $actual->merchantId(),
            'externalReference' => $actual->debtorExternalId(),
            'isActive' => $actual->isActive(),
        ];

        $this->assertEquals($expected, $actual);
    }
}
