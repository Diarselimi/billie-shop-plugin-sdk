<?php

namespace App\DomainModel\FraudRules;

class FraudRuleEntityFactory
{
    public function createFromDatabaseRow(array $row): FraudRuleEntity
    {
        return (new FraudRuleEntity())
            ->setIncludedWords(json_decode($row['included_words']))
            ->setExcludedWords(json_decode($row['excluded_words']))
            ->setCheckForPublicDomain($row['check_email_public_domain'])
            ->setId($row['id']);
    }

    public function createFromDatabaseRows(array $rows): array
    {
        return array_map(function ($row) {
            return $this->createFromDatabaseRow($row);
        }, $rows);
    }
}
