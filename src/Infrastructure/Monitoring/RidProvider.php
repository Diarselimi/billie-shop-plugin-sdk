<?php

namespace App\Infrastructure\Monitoring;

use App\Http\HttpConstantsInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class RidProvider
{
    private $request;

    public function __construct(RequestStack $requestStack)
    {
        $this->request = $requestStack;
    }

    public function getRid():? string
    {
        return $this->request->getCurrentRequest()
            ? $this->request->getCurrentRequest()->headers->get(HttpConstantsInterface::REQUEST_HEADER_RID)
            : null
        ;
    }

    public function getShortRid():? string
    {
        return substr($this->getRid(), strlen($this->getRid()) - 4);
    }
}
