<?php

namespace App\Tests\Functional\Context;

use Behat\Behat\Context\Context;
use donatj\MockWebServer\MockWebServer;

class MockServerContext implements Context
{
    private const SERVER_PORT = 7070;

    private static $server;

    /**
     * @BeforeSuite
     */
    public static function startServer()
    {
        self::$server = new MockWebServer(self::SERVER_PORT);
        self::$server->start();

        register_shutdown_function(
            function () {
                self::stopServer();
            }
        );
    }

    /**
     * @AfterSuite
     */
    public static function stopServer()
    {
        self::$server->stop();
    }

    public static function getServer(): MockWebServer
    {
        if (!self::$server) {
            self::startServer();
        }

        return self::$server;
    }
}
