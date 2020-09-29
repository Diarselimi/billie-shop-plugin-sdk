<?php

namespace App\DomainModel\DebtorCompany;

class MostSimilarCandidateDTOFactory
{
    public function createFromAlfredResponse(array $decodedResponse): MostSimilarCandidateDTO
    {
        $candidate = $decodedResponse['most_similar_candidate'] ?? null;
        if ($candidate === null) {
            return new NullMostSimilarCandidateDTO();
        }

        return new MostSimilarCandidateDTO(
            $candidate['uuid'],
            $candidate['name'],
            $candidate['registration_number'],
            $candidate['crefo_id'],
            $candidate['schufa_id'],
            $candidate['google_places_id'],
            $candidate['tax_id'],
            $candidate['address_house'],
            $candidate['address_street'],
            $candidate['address_city'],
            $candidate['address_postal_code'],
            $candidate['address_country']
        );
    }
}
