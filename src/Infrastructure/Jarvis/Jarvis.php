<?php

namespace App\Infrastructure\Jarvis;

use App\DomainModel\DebtorScoring\DebtorScoringRequestDTO;
use App\DomainModel\DebtorScoring\DebtorScoringServiceRequestException;
use App\DomainModel\DebtorScoring\ScoringServiceInterface;
use App\Infrastructure\ClientResponseDecodeException;
use App\Infrastructure\DecodeResponseTrait;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\TransferStats;

class Jarvis implements ScoringServiceInterface, LoggingInterface
{
    use LoggingTrait, DecodeResponseTrait;

    private const SCORING_REQUEST_TIMEOUT = 15;

    private $client;

    public function __construct(Client $jarvisClient)
    {
        $this->client = $jarvisClient;
    }

    public function isEligibleForPayAfterDelivery(DebtorScoringRequestDTO $requestDTO): bool
    {
        try {
            $response = $this->client->get("/debtor-scoring/{$requestDTO->getDebtorUuid()}", [
                'query' => [
                    'is_sole_trader' => $requestDTO->isSoleTrader(),
                    'has_paid_invoice' => $requestDTO->isHasPaidInvoice(),
                    'crefo_low_score_threshold' => $requestDTO->getCrefoLowScoreThreshold(),
                    'crefo_high_score_threshold' => $requestDTO->getCrefoHighScoreThreshold(),
                    'schufa_low_score_threshold' => $requestDTO->getSchufaLowScoreThreshold(),
                    'schufa_average_score_threshold' => $requestDTO->getSchufaAverageScoreThreshold(),
                    'schufa_high_score_threshold' => $requestDTO->getSchufaHighScoreThreshold(),
                    'schufa_sole_trader_score_threshold' => $requestDTO->getSchufaSoleTraderScoreThreshold(),
                ],
                'on_stats' => function (TransferStats $stats) {
                    $this->logServiceRequestStats($stats, 'score_debtor');
                },
                'timeout' => self::SCORING_REQUEST_TIMEOUT,
            ]);

            return $this->decodeResponse($response)['is_eligible'];
        } catch (TransferException | ClientResponseDecodeException $exception) {
            throw new DebtorScoringServiceRequestException($exception);
        }
    }
}
