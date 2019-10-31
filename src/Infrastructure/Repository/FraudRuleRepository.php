<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\FraudRules\FraudRuleEntity;
use App\DomainModel\FraudRules\FraudRuleEntityFactory;
use App\DomainModel\FraudRules\FraudRuleRepositoryInterface;
use Billie\PdoBundle\Infrastructure\Pdo\AbstractPdoRepository;

class FraudRuleRepository extends AbstractPdoRepository implements FraudRuleRepositoryInterface
{
    public const TABLE_NAME = 'risk_check_rules';

    private const SELECT_FIELDS = [
        'id',
        'included_words',
        'excluded_words',
        'check_email_public_domain',
        'updated_at',
        'created_at',
    ];

    private $factory;

    public function __construct(FraudRuleEntityFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @return FraudRuleEntity[]
     */
    public function getAll(): array
    {
        $sql = 'SELECT ' . implode(', ', self::SELECT_FIELDS) . ' FROM ' . self::TABLE_NAME;
        $rows = $this->doFetchAll($sql);

        return $this->factory->createFromDatabaseRows($rows);
    }
}
