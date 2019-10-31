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

    public function insert(FraudRuleEntity $fraudRuleEntity): void
    {
        $sql = $this->generateInsertQuery(self::TABLE_NAME, self::SELECT_FIELDS);

        $now = new \DateTime();
        $id = $this->doInsert($sql, [
            'included_words' => json_encode($fraudRuleEntity->getIncludedWords()),
            'excluded_words' => json_encode($fraudRuleEntity->getExcludedWords()),
            'check_email_public_domain' => (int) $fraudRuleEntity->isCheckForPublicDomainEnabled(),
            'created_at' => $now->format(self::DATE_FORMAT),
            'updated_at' => $now->format(self::DATE_FORMAT),
        ]);

        $fraudRuleEntity->setId($id);
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

    protected function generateInsertQuery(string $tableName, array $fields): string
    {
        if ($fields[0] === 'id') {
            array_shift($fields);
        }

        $values = array_map(function (string $field) {
            return ':'.$field;
        }, $fields);

        return 'INSERT INTO '.$tableName.' ('.implode(', ', $fields).') VALUES ('.implode(', ', $values).')';
    }
}
