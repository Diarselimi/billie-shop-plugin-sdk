<?php

namespace App\DomainModel\CheckoutSession;

/**
 * @deprecated use CheckoutSessionRepository
 */
interface CheckoutSessionRepositoryInterface
{
    public function create(CheckoutSessionEntity $entity): CheckoutSessionEntity;

    /**
     * Once we have to get a session let's try to create a method in the new repo:
     *  CheckoutSessionRepository::findByToken(Token $token): CheckoutSession
     */
    public function findOneById(int $id): ?CheckoutSessionEntity;

    /**
     * Read the comment above
     */
    public function findOneByUuid(string $uuid): ?CheckoutSessionEntity;

    /**
     * Once we need to invalidate a session, we should do (using our new CheckoutSession):
     *   $session->deactivate();
     *   $repo->save($session);
     */
    public function invalidateById(int $id): bool;

    /**
     * Once we need to reactivate a session, we should do (using our new CheckoutSession):
     *   $session->reactivate();
     *   $repo->save($session);
     */
    public function reActivateSession(string $sessionUuid): void;
}
