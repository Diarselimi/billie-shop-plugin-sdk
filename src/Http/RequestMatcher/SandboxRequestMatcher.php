<?php

declare(strict_types=1);

namespace App\Http\RequestMatcher;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;

class SandboxRequestMatcher implements RequestMatcherInterface
{
    private $paellaSandboxUrl;

    public function __construct(string $paellaSandboxUrl)
    {
        $this->paellaSandboxUrl = $paellaSandboxUrl;
    }

    public function matches(Request $request): bool
    {
        if ($this->paellaSandboxUrl) {
            return false;
        }

        return true;
    }
}
