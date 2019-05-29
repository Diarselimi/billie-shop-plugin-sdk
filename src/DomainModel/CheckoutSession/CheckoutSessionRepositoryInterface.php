<?php

namespace App\DomainModel\CheckoutSession;

interface CheckoutSessionRepositoryInterface
{
    public function create(CheckoutSessionEntity $entity): CheckoutSessionEntity;

    public function findOneById(int $id): ?CheckoutSessionEntity;

    public function findOneByUuid(string $uuid): ?CheckoutSessionEntity;

    public function invalidateById(int $id): bool;
}
