<?php

namespace App\Infrastructure\PDO;

use App\DomainModel\Monitoring\LoggingInterface;
use App\DomainModel\Monitoring\LoggingTrait;

class PDO extends \PDO implements LoggingInterface
{
    use LoggingTrait;

    public const ERROR_GONE_AWAY = 2006;
    public const ERROR_LOST_CONNECTION = 2013;

    private $dsn;
    private $username;
    private $passwd;
    private $options;

    public function __construct(string $dsn, string $username = null, string $passwd = null, array $options = [])
    {
        if (!isset($options[self::ATTR_ERRMODE])) {
            $options[self::ATTR_ERRMODE] = self::ERRMODE_EXCEPTION;
        }

        $this->dsn = $dsn;
        $this->username = $username;
        $this->passwd = $passwd;
        $this->options = $options;

        parent::__construct($dsn, $username, $passwd, $options);
    }

    public function reconnect()
    {
        parent::__construct($this->dsn, $this->username, $this->passwd, $this->options);
    }
}
