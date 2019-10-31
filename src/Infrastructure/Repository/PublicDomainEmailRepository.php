<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\PublicDomain\PublicDomainEmailRepositoryInterface;
use Billie\PdoBundle\Infrastructure\Pdo\AbstractPdoRepository;

class PublicDomainEmailRepository extends AbstractPdoRepository implements PublicDomainEmailRepositoryInterface
{
    public const TABLE_NAME = 'public_domains';

    private const SELECT_FIELDS = [
        'id',
        'domain',
        'created_at',
    ];

    public function isKnownAsPublicDomain(string $domain): bool
    {
        $domain = mb_strtolower($domain);
        $tableName = self::TABLE_NAME;

        $sql = "SELECT count(*) as `total` FROM {$tableName} WHERE domain = :domain_name ";
        $result = $this->doFetchOne($sql, ['domain_name' => $domain]);

        return $result['total'] > 0;
    }
}
