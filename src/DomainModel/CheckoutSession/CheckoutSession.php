<?php

namespace App\DomainModel\CheckoutSession;

class CheckoutSession
{
    private int $id;

    private Token $token;

    private Context $context;

    private int $merchantId;

    private ?string $debtorExternalId;

    private bool $isActive = true;

    public function __construct(Token $token, Context $context, int $merchantId, ?string $debtorExternalId)
    {
        $this->token = $token;
        $this->context = $context;
        $this->merchantId = $merchantId;
        $this->debtorExternalId = $debtorExternalId;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function token(): Token
    {
        return $this->token;
    }

    public function context(): Context
    {
        return $this->context;
    }

    public function merchantId(): int
    {
        return $this->merchantId;
    }

    public function debtorExternalId(): ?string
    {
        return $this->debtorExternalId;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function activate(): void
    {
        $this->isActive = true;
    }

    public function deactivate(): void
    {
        $this->isActive = false;
    }
}
