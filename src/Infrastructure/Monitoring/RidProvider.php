<?php

namespace App\Infrastructure\Monitoring;

use App\Http\HttpConstantsInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\RequestStack;

class RidProvider
{
    private $request;
    private $internalRid;

    public function __construct(RequestStack $requestStack)
    {
        $this->request = $requestStack;
    }

    public function getRid():? string
    {
        return $this->request->getCurrentRequest()
            ? $this->request->getCurrentRequest()->headers->get(HttpConstantsInterface::REQUEST_HEADER_RID)
            : $this->useInternalRid()
        ;
    }

    public function getShortRid():? string
    {
        return substr($this->getRid(), strlen($this->getRid()) - 4);
    }

    private function useInternalRid(): string
    {
        return $this->internalRid ? $this->internalRid : $this->internalRid = Uuid::uuid4()->toString();
    }
}
