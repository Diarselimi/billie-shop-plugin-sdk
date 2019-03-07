<?php

use donatj\MockWebServer\MockWebServer;
use donatj\MockWebServer\ResponseStack;
use Symfony\Component\HttpKernel\KernelInterface;

trait MockServerTrait
{
    /**
     * @var MockWebServer
     */
    private $server;

    public function setServer(KernelInterface $kernel)
    {
        $this->server = $kernel->getContainer()->get(MockWebServer::class);
        $this->server->start();
    }

    public function stopServer()
    {
        $this->server->stop();
    }

    public function setMock($url, ResponseStack $responseStack)
    {
        $this->server->setResponseOfPath($url, $responseStack);
    }
}
