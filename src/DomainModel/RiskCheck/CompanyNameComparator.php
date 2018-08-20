<?php

namespace App\DomainModel\RiskCheck;

class CompanyNameComparator
{
    const LEGAL_FORMS_SEPARATED = ['gmbh', 'gesellschaft mit beschränkter haftung', 'mit beschränkter haftung', 'freie berufe', 'gewerbebetrieb', 'gbr', 'ltd', 'gbr / arge', 'einzelfirma', 'ohg', 'kg', 'ag', 'ug', 'eg', 'se', 'kommanditgesellschaft', 'aktiengesellschaft', 'eingetragene genossenschaft', 'verein', 'kgaa', 'kommanditgesellschaft auf aktien', 'vvag', 'körperschaft öffentlichen rechts', 'stiftung', 'anstalt öffentlichen rechts', 'landwirtschaftlicher betrieb', 'partnerschaftsgesellschaft', 'partg mbb', 'ewiv', 'limited', 'ug (haftungsbeschränkt)', '(haftungsbeschränkt)', 'sarl', 'gesellschaft mbh', 'gesellschaft', 'limited', 'sarl', '& co. kg', '& co.kg', '& co. kgaa', 'und co. kg', 'und co. kgaa', '& co kg', '& co kgaa', 'und co kg', 'und co kgaa', '+ co kg', '+ co kgaa', '&co.kg', '&co.kgaa', '&co.', '+co.', 'kg&co.kg', 'ag&co.kg', 'ug&co.kg', 'eg&co.kg', 'se&co.kg', 'kg&co.kgaa', 'ag&co.kgaa', 'ug&co.kgaa', 'eg&co.kgaa', 'se&co.kgaa', 'gemeinnützige gmbh', 'ggmbh', 'e. k.', 'e.k.', 'ltd. & co. kg', 'e. v.', 'e.v.', 'sàrl', 'GmbH&Co.KG', 'mbH'];

    const LEGAL_FORMS_JOINED = ['gesellschaft mit beschränkter haftung', 'gesellschaft mbh', 'gesellschaft'];

    const PERCENTAGE_OF_SIMILAR_WORD = 66;
    const PERCENTAGE_OF_SIMILAR_NAME = 75;

    public function compareWithCompanyName(string $companyName1, string $companyName2): bool
    {
        if (empty($companyName1) && empty($companyName2)) {
            return true;
        } elseif (empty($companyName1) || empty($companyName2)) {
            return false;
        }

        return $this->areSimilarNames(
            $this->getMeaningfulWords($companyName1),
            $this->getMeaningfulWords($companyName2)
        );
    }

    public function compareWithPersonName(string $companyName, string $personFirstName, string $personLastName): bool
    {
        if (empty($companyName) && empty($personFirstName) && empty($personLastName)) {
            return true;
        } elseif (empty($companyName) || empty($personFirstName) || empty($personLastName)) {
            return false;
        }

        return \stripos($companyName, $personFirstName) !== false && \stripos($companyName, $personLastName) !== false;
    }

    private function getMeaningfulWords(string $name): array
    {
        return $this->splitWords($this->removeLegalForms($name));
    }

    private function removeLegalForms(string $name): string
    {
        return $this->removeLegalFormsJoined($this->removeLegalFormsSeparated($name));
    }

    private function removeLegalFormsSeparated(string $name): string
    {
        $legalForms = array_map(function ($value) {
            return " $value ";
        }, self::LEGAL_FORMS_SEPARATED);

        return $this->removeFromListOfLegalForms($legalForms, $name);
    }

    private function removeLegalFormsJoined(string $name): string
    {
        $legalForms = array_map(function ($value) {
            return "$value ";
        }, self::LEGAL_FORMS_JOINED);

        return $this->removeFromListOfLegalForms($legalForms, $name);
    }

    private function removeFromListOfLegalForms(array $legalForms, string $name)
    {
        usort($legalForms, function ($value1, $value2) {
            return strlen($value1) < strlen($value2);
        });

        return str_ireplace($legalForms, ' ', "$name ");
    }

    private function splitWords(string $name): array
    {
        return array_filter(preg_split("/[^0-9a-zA-Z\x7f-\xff]/", $name));
    }

    private function areWordsSimilar(string $word1, string $word2): bool
    {
        similar_text(strtolower($word1), strtolower($word2), $percentage);

        return $percentage >= self::PERCENTAGE_OF_SIMILAR_WORD;
    }

    private function isSimilarWordInName(string $word, array $wordsOfName): bool
    {
        foreach ($wordsOfName as $wordOfName) {
            if ($this->areWordsSimilar($word, $wordOfName)) {
                return true;
            }
        }

        return false;
    }

    private function countSimilarWords(array $wordsOfName1, array $wordsOfName2): int
    {
        $similarCount = 0;
        foreach ($wordsOfName1 as $wordOfName1) {
            if ($this->isSimilarWordInName($wordOfName1, $wordsOfName2)) {
                $similarCount++;
            }
        }

        return $similarCount;
    }

    private function areSimilarNames(array $wordsOfName1, array $wordsOfName2): bool
    {
        $numberSimilarWords = $this->countSimilarWords($wordsOfName1, $wordsOfName2);
        $percentageMatching = $numberSimilarWords / count($wordsOfName1) * 100;

        return $percentageMatching >= self::PERCENTAGE_OF_SIMILAR_NAME;
    }
}
