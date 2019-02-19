<?php

namespace App\Infrastructure\PDO;

use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use PDOException;

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
        $stmt = null;

        try {
            $stmt = $this->pdo->prepare($query, []);
            $stmt->execute($parameters);

            $this->logInfo('[pdo] Query succeeded');
        } catch (PDOException $exception) {
            if (
                !isset($exception->errorInfo[1])
                || !in_array($exception->errorInfo[1], [PDO::ERROR_GONE_AWAY, PDO::ERROR_LOST_CONNECTION])
            ) {
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
                } catch (PDOException $exception) {
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
