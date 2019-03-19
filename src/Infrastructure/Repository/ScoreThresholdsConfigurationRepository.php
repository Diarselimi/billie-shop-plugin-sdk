<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationEntity;
use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationEntityFactory;
use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationRepositoryInterface;
use Billie\PdoBundle\Infrastructure\Pdo\AbstractPdoRepository;

class ScoreThresholdsConfigurationRepository extends AbstractPdoRepository implements ScoreThresholdsConfigurationRepositoryInterface
{
    const SELECT_FIELDS = '
        id,
        crefo_low_score_threshold,
        crefo_high_score_threshold,
        schufa_low_score_threshold,
        schufa_average_score_threshold,
        schufa_high_score_threshold,
        schufa_sole_trader_score_threshold,
        created_at,
        updated_at'
    ;

    private $entityFactory;

    public function __construct(ScoreThresholdsConfigurationEntityFactory $entityFactory)
    {
        $this->entityFactory = $entityFactory;
    }

    public function insert(ScoreThresholdsConfigurationEntity $entity): void
    {
        $id = $this->doInsert('
            INSERT INTO score_thresholds_configuration
            (
              crefo_low_score_threshold,
              crefo_high_score_threshold,
              schufa_low_score_threshold,
              schufa_average_score_threshold,
              schufa_high_score_threshold,
              schufa_sole_trader_score_threshold, 
              created_at, 
              updated_at
            )
            VALUES
            (
              :crefo_low_score_threshold,
              :crefo_high_score_threshold,
              :schufa_low_score_threshold,
              :schufa_average_score_threshold,
              :schufa_high_score_threshold,
              :schufa_sole_trader_score_threshold, 
              :created_at, 
              :updated_at
            )
        ', [
            'crefo_low_score_threshold' => $entity->getCrefoLowScoreThreshold(),
            'crefo_high_score_threshold' => $entity->getCrefoHighScoreThreshold(),
            'schufa_low_score_threshold' => $entity->getSchufaLowScoreThreshold(),
            'schufa_average_score_threshold' => $entity->getSchufaAverageScoreThreshold(),
            'schufa_high_score_threshold' => $entity->getSchufaHighScoreThreshold(),
            'schufa_sole_trader_score_threshold' => $entity->getSchufaSoleTraderScoreThreshold(),
            'created_at' => $entity->getCreatedAt()->format(self::DATE_FORMAT),
            'updated_at' => $entity->getUpdatedAt()->format(self::DATE_FORMAT),
        ]);

        $entity->setId($id);
    }

    public function getById(int $id): ? ScoreThresholdsConfigurationEntity
    {
        $row = $this->doFetchOne(
            'SELECT ' . self::SELECT_FIELDS . ' FROM score_thresholds_configuration WHERE id = :id',
            ['id' => $id]
        );

        return $row ? $this->entityFactory->createFromDatabaseRow($row) : null;
    }
}
