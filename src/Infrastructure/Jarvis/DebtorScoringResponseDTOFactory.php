<?php

declare(strict_types=1);

namespace App\Infrastructure\Jarvis;

use App\DomainModel\DebtorScoring\DebtorScoringResponseDTO;

class DebtorScoringResponseDTOFactory
{
    public function createFailed()
    {
        return (new DebtorScoringResponseDTO())
            ->setHasFailed(true)
            ->setIsEligible(false);
    }

    public function createFromJarvisResponse(array $response): DebtorScoringResponseDTO
    {
        return (new DebtorScoringResponseDTO())
            ->setDecisionUuid($response['decision_uuid'] ?? null)
            ->setHasFailed($this->hasFailed($response))
            ->setIsEligible(
                (bool) $response['is_eligible'] ?? false
            );
    }

    private function hasFailed(array $response): bool
    {
        $hasFailed = !isset($response['is_eligible']);

        if (!isset($response['scores']) || !is_array($response['scores'])) {
            return $hasFailed;
        }

        foreach ($response['scores'] as $score) {
            if (($score['status'] ?? '') === 'failed') {
                return true;
            }
        }

        return $hasFailed;
    }
}
