<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\CheckoutSession\CheckoutSession;
use App\DomainModel\CheckoutSession\CheckoutSessionRepository;
use App\DomainModel\CheckoutSession\Country;
use App\DomainModel\CheckoutSession\Token;
use Billie\PdoBundle\Infrastructure\Pdo\AbstractPdoRepository;

class CheckoutSessionPdoRepository extends AbstractPdoRepository implements CheckoutSessionRepository
{
    use HydratorTrait;

    public function findById(int $id): ?CheckoutSession
    {
        return $this->findOneWith('id', $id);
    }

    public function findByToken(Token $token): ?CheckoutSession
    {
        return $this->findOneWith('uuid', (string) $token);
    }

    public function save(CheckoutSession $checkoutSession): void
    {
        if (null === $this->findByToken($checkoutSession->token())) {
            $this->insert($checkoutSession);

            return;
        }

        $this->update($checkoutSession);
    }

    private function findOneWith(string $field, $value): ?CheckoutSession
    {
        $row = $this->doFetchOne(
            "SELECT * FROM `checkout_sessions` WHERE `$field` = :bind",
            ['bind' => $value],
        );

        return $this->convertFromDbRow($row);
    }

    private function convertFromDbRow(?array $row): ?CheckoutSession
    {
        if (null === $row) {
            return  null;
        }

        $debtorExternalId = $row['merchant_debtor_external_id'];
        $debtorExternalId = '' === $debtorExternalId ? null : $debtorExternalId;

        return $this->hydrate(CheckoutSession::class, [
            'id' => $row['id'],
            'token' => Token::fromHash($row['uuid']),
            'country' => new Country('DE'),
            'merchantId' => (int) $row['merchant_id'],
            'debtorExternalId' => $debtorExternalId,
            'isActive' => $row['is_active'] === '1',
        ]);
    }

    private function insert(CheckoutSession $checkoutSession): void
    {
        $sql = <<<SQL
            INSERT INTO `checkout_sessions` (`uuid`, `merchant_id`, `merchant_debtor_external_id`, `is_active`, `created_at`, `updated_at`) 
            VALUES (:uuid, :merchant, :merchant_debtor_external_id, :is_active, :created_at, :updated_at)
            SQL;

        $this->doInsert($sql, [
            'uuid' => (string) $checkoutSession->token(),
            'merchant' => $checkoutSession->merchantId(),
            'merchant_debtor_external_id' => $checkoutSession->debtorExternalId() ?? '',
            'is_active' => $checkoutSession->isActive() ? '1' : '0',
            'created_at' => (new \DateTime())->format(self::DATE_FORMAT),
            'updated_at' => (new \DateTime())->format(self::DATE_FORMAT),
        ]);
    }

    private function update(CheckoutSession $checkoutSession): void
    {
        $sql = <<<SQL
            UPDATE `checkout_sessions`
            SET is_active = :is_active, updated_at = :updated_at
            WHERE uuid = :uuid
            SQL;

        $this->doUpdate($sql, [
            'uuid' => (string) $checkoutSession->token(),
            'is_active' => $checkoutSession->isActive() ? '1' : '0',
            'updated_at' => (new \DateTime())->format(self::DATE_FORMAT),
        ]);
    }
}
