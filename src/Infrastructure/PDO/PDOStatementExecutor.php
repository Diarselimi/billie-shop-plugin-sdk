<?php

namespace App\Infrastructure\PDO;

use App\DomainModel\Monitoring\LoggingInterface;
use App\DomainModel\Monitoring\LoggingTrait;

class PDOStatementExecutor implements LoggingInterface
{
    use LoggingTrait;

    private const MAX_RETRIES = 3;
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function executeWithReconnect(string $query, array $parameters = [])
    {
        try {
            $stmt = $this->pdo->prepare($query, []);
            $stmt->execute($parameters);

            $this->logInfo('[pdo] Query succeeded');
        } catch (\PDOException|\ErrorException $exception) {
            if (!\in_array($stmt->errorInfo()[1], [PDO::ERROR_GONE_AWAY, PDO::ERROR_LOST_CONNECTION])) {
                $this->logError('[pdo] Unhandled exception', ['exception' => $exception]);

                throw $exception;
            }

            $retries = 0;
            $this->logError('[pdo] Connection lost exception, trying to recover', ['exception' => $exception]);

            while ($retries < self::MAX_RETRIES) {
                $this->logError('[pdo] Reconnect attempt {count}', [
                    'exception' => $exception,
                    'count' => $retries + 1,
                ]);

                try {
                    $this->pdo->reconnect();

                    $stmt = $this->pdo->prepare($query, []);
                    $stmt->execute($parameters);

                    $this->logInfo('[pdo] Query succeeded on {count} retry', ['count' => $retries]);

                    return $stmt;
                } catch (\PDOException|\ErrorException $exception) {
                    $this->logError('[pdo] Reconnect attempt {count} failed', [
                        'exception' => $exception,
                        'count' => $retries + 1,
                    ]);

                    $retries++;
                }
            }

            $this->logError('[pdo] Max reconnection attempts reached, give up now');

            throw $exception;
        }

        return $stmt;
    }
}
