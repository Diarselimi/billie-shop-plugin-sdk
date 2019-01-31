<?php

namespace App\DomainModel\ScoreThresholdsConfiguration;

interface ScoreThresholdsConfigurationRepositoryInterface
{
    public function insert(ScoreThresholdsConfigurationEntity $entity): void;

    public function getById(int $id): ? ScoreThresholdsConfigurationEntity;
}
