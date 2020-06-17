<?php

declare(strict_types=1);

namespace App\Http;

use Symfony\Component\HttpFoundation\RequestStack;

class IpAddressProvider
{
    public const IP_ADDRESS = 'X-Real-IP';

    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getIpAddress(): ?string
    {
        if (PHP_SAPI === 'cli') {
            return null;
        }

        return $this->requestStack->getCurrentRequest()->headers->get(self::IP_ADDRESS);
    }
}
