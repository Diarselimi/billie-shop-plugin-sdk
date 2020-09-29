<?php

declare(strict_types=1);

namespace App\DomainModel\DebtorCompany;

class IdentifyDebtorResponseDTOFactory
{
    private const IDENTIFIED_COMPANY = 'identified_company';

    private const SUGGESTIONS = 'suggestions';

    private $mostSimilarCandidateDTOFactory;

    private $debtorCompanyFactory;

    public function __construct(
        MostSimilarCandidateDTOFactory $mostSimilarCandidateDTOFactory,
        DebtorCompanyFactory $debtorCompanyFactory
    ) {
        $this->mostSimilarCandidateDTOFactory = $mostSimilarCandidateDTOFactory;
        $this->debtorCompanyFactory = $debtorCompanyFactory;
    }

    public function createFromCompaniesServiceResponse(array $decodedResponse, bool $isStrictMatch = true): IdentifyDebtorResponseDTO
    {
        $this->mutateDecodedResponse($decodedResponse);
        $mostSimilarCandidateDTO = $this->mostSimilarCandidateDTOFactory->createFromAlfredResponse($decodedResponse);

        if ($decodedResponse[self::IDENTIFIED_COMPANY] === null) {
            return new IdentifyDebtorResponseDTO(null, $mostSimilarCandidateDTO);
        }

        return new IdentifyDebtorResponseDTO(
            $this->debtorCompanyFactory->createIdentifiedFromAlfredResponse($decodedResponse, $isStrictMatch),
            $mostSimilarCandidateDTO
        );
    }

    private function mutateDecodedResponse(array &$decodedResponse)
    {
        $suggestions = $decodedResponse[self::SUGGESTIONS] ?? [];

        if (count($suggestions) > 0) {
            $decodedResponse[self::IDENTIFIED_COMPANY] = $suggestions[0];
        }
    }
}
