<?php

namespace App\DomainModel\SepaB2BGenerator;

interface DocumentGeneratorClientInterface
{
    public function generate(SepaB2BDocumentGenerationRequestDTO $b2BGeneratorDTO): string;
}
