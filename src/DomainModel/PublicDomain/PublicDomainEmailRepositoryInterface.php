<?php

namespace App\DomainModel\PublicDomain;

interface PublicDomainEmailRepositoryInterface
{
    public function isKnownAsPublicDomain(string $domain): bool;
}
