<?php

namespace App\DomainModel\IdentifyAndRemoveWrongIdentifications;

use App\DomainModel\CompanySimilarity\CompanySimilarityServiceInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Billie\PdoBundle\Infrastructure\Pdo\PdoConnection;

class IdentifyAndRemoveWrongIdentificationService implements LoggingInterface
{
    use LoggingTrait;

    private PdoConnection $db;

    private CompanySimilarityServiceInterface $companySimilarityService;

    private static $legalForms = [
        'Freie Berufe' => 'Freie Berufe',
        'Gewerbebetrieb' => 'Gewerbebetrieb',
        'GbR' => 'Gesellschaft bürgerlichen Rechts',
        'GbR / ARGE' => 'GbR / Arbeitsgemeinschaft',
        'Einzelfirma' => 'Einzelfirma',
        'OHG' => 'Offene Handelsgesellschaft',
        'KG' => 'Kommanditgesellschaft',
        'GmbH' => 'Gesellschaft mit beschränkter Haftung',
        'AG' => 'Aktiengesellschaft',
        'eG' => 'eingetragene Genossenschaft',
        'Verein' => 'Verein',
        'KGaA' => 'Kommanditgesellschaft auf Aktien',
        'VVaG' => 'Versicherungsverein auf Gegenseitigkeit',
        'Körperschaft öffentlichen Rechts' => 'Körperschaft öffentlichen Rechts',
        'Stiftung' => 'Stiftung',
        'Anstalt öffentlichen Rechts' => 'Anstalt öffentlichen Rechts',
        'Landwirtschaftlicher Betrieb' => 'Landwirtschaftlicher Betrieb',
        'Partnerschaftsgesellschaft' => 'Partnerschaftsgesellschaft',
        'PartG mbB' => 'Partnerschaftsgesellschaft mit beschränkter Berufshaftung',
        'EWIV' => 'Europäische Wirtschaftliche Interessenvereinigung',
        'Limited' => 'Limited',
        'UG' => 'Unternehmergesellschaft (haftungsbeschränkt)',
        'Sarl' => 'Société à responsabilité limitée',
        'Gesellschaft & Co. KGaA' => 'Gesellschaft & Co. KGaA',
        'GmbH & Co. KGaA' => 'GmbH & Co. KGaA',
        'AG & Co. KGaA' => 'AG & Co. KGaA',
        'KG & Co. KGaA' => 'KG & Co. KGaA',
        'Limited & Co. KGaA' => 'Limited & Co. KGaA',
        'Sarl & Co. KGaA' => 'Sarl & Co. KGaA',
        'GmbH & Co. KG' => 'GmbH & Co. KG',
        'AG & Co. KG' => 'AG & Co. KG',
        'eG & Co. KG' => 'eG & Co. KG',
        'Verein & Co. KG' => 'Verein & Co. KG',
        'GbR & Co. KG' => 'GbR & Co. KG',
        'GmbH & Co. KG & Co. KG' => 'GmbH & Co. KG & Co. KG',
        'AG & Co. KG & Co. KG' => 'AG & Co. KG & Co. KG',
        'OHG & Co. KG' => 'OHG & Co. KG',
        'KG & Co. KG' => 'KG & Co. KG',
        'Sarl & Co. KG' => 'Sarl & Co. KG',
        'Limited & Co. KG' => 'Limited & Co. KG',
        'Gesellschaft & Co. KG' => 'Gesellschaft & Co. KG',
        'UG & Co. KG' => 'UG (haftungsbeschränkt) & Co. KG',
        'UG & Co. KGaA' => 'UG (haftungsbeschränkt) & Co. KGaA',
        'SE & Co. KG' => 'SE & Co. KG',
        'SE & Co. KGaA' => 'SE & Co. KGaA',
        'Stiftung & Co. KG' => 'Stiftung & Co. KG',
        'Stiftung & Co. KGaA' => 'Stiftung & Co. KGaA',
        'SE' => 'Societas Europaea',
        'international_sole_trader' => 'international_sole_trader',
        'international_limited_company' => 'international_limited_company',
        'international_institute' => 'international_institute',
        'Einzelunternehmer' => 'Einzelunternehmer (ohne HR-Eintrag)',
        'Öffentliche Einrichtung' => 'Öffentliche Einrichtung',
        'Sonstige' => 'Sonstige',
    ];

    public function __construct(PdoConnection $db, CompanySimilarityServiceInterface $companySimilarityService)
    {
        $this->db = $db;
        $this->companySimilarityService = $companySimilarityService;
    }

    public function process(array $data, int $merchantId, bool $isDryRun): array
    {
        $output = array_merge($data, [
            'identified_company_id' => null,
            'identified_name' => null,
            'found' => 0,
            'matches' => 0,
            'unlinked' => 0,
        ]);

        $identifiedCompany = $this->findIdentifiedCompany($data['external_id'], $merchantId);

        if ($identifiedCompany === null) {
            return $output;
        }

        $output['identified_company_id'] = $identifiedCompany['company_id'];
        $output['identified_name'] = $identifiedCompany['company_name'];

        $output['found'] = 1;
        if ($this->identificationIsCorrect($data, $identifiedCompany)) {
            $output['matches'] = 1;

            return $output;
        }

        $this->unlinkKnownCustomerCheck($data['external_id'], $isDryRun);
        $output['unlinked'] = 1;

        return $output;
    }

    private function findIdentifiedCompany(string $merchantExternalId, int $merchantId): ?array
    {
        $databasePrefix = getenv('DB_PREFIX');

        $sql = <<<SQL
SELECT
    `webapp{$databasePrefix}`.company_snapshots.company_id as company_id,
    `webapp{$databasePrefix}`.company_snapshots.name as company_name,
    `webapp{$databasePrefix}`.ref_legal_forms.name as legal_form,
    'DE' as address_country,
    `webapp{$databasePrefix}`.addresses.city as address_city,
    `webapp{$databasePrefix}`.addresses.postal_code as address_postal_code,
    `webapp{$databasePrefix}`.addresses.street_address as address_street,
    `webapp{$databasePrefix}`.addresses.house_number as address_house,
    `webapp{$databasePrefix}`.addresses.additional_info as address_addition,
    null as tax_id,
    null as tax_number,
    null as registration_court,
    null as registration_number,
    null as industry_sector,
    null as subindustry_sector,
    null as employees_number
FROM `webapp{$databasePrefix}`.company_snapshots
INNER JOIN `webapp{$databasePrefix}`.addresses ON `webapp{$databasePrefix}`.company_snapshots.bureau_provided_address_id = `webapp{$databasePrefix}`.addresses.id
INNER JOIN `webapp{$databasePrefix}`.ref_legal_forms ON `webapp{$databasePrefix}`.ref_legal_forms.id = `webapp{$databasePrefix}`.company_snapshots.legal_form_id
INNER JOIN merchants_debtors ON merchants_debtors.debtor_id = `webapp{$databasePrefix}`.company_snapshots.company_id
INNER JOIN orders ON orders.merchant_debtor_id = merchants_debtors.id AND orders.state NOT IN ('new', 'declined')
INNER JOIN debtor_external_data ON debtor_external_data.id = orders.debtor_external_data_id
WHERE 
    orders.merchant_id = :merchant_id
    AND debtor_external_data.merchant_external_id = :merchant_external_id
    AND `webapp{$databasePrefix}`.company_snapshots.is_current = 1
GROUP BY merchants_debtors.debtor_id
SQL;

        $this->logInfo("Selecting the companies", [
            'merchant_external_id' => $merchantExternalId,
            'merchant_id' => $merchantId,
        ]);
        $this->logDebug("Select SQL: " . $sql);

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'merchant_external_id' => $merchantExternalId,
            'merchant_id' => $merchantId,
        ]);

        $count = $stmt->rowCount();
        $this->logInfo("Select executed, found {$stmt->rowCount()} records");

        if ($count === 0) {
            return null;
        }

        if ($count > 1) {
            throw new \Exception("Unexpected number of debtors: {$count} instead of 1");
        }

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    private function identificationIsCorrect(array $external, array $identified): bool
    {
        $preparedIdentified = $identified;
        unset($preparedIdentified['company_id']);
        $preparedIdentified['candidate_id'] = 1;

        $result = $this->companySimilarityService
            ->match($this->transformExternalData($external), $preparedIdentified);

        return $result['company_similarities'][0]['is_match'];
    }

    private function unlinkKnownCustomerCheck(string $merchantExternalId, bool $isDryRun): void
    {
        $sql = <<<SQL
UPDATE 
    debtor_external_data
SET 
    merchant_external_id = :merchant_external_id_to, 
    debtor_data_hash = :debtor_data_hash
WHERE 
    merchant_external_id = :merchant_external_id_from;
SQL;
        $this->logInfo('Unlink the customer check', [
            'merchant_external_id_from' => $merchantExternalId,
            'merchant_external_id_to' => $merchantExternalId . '-invalidated',
            'debtor_data_hash' => 'INVALID_HASH',
        ]);
        $this->logDebug("Unlink SQL: $sql");

        if (!$isDryRun) {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'merchant_external_id_from' => $merchantExternalId,
                'merchant_external_id_to' => $merchantExternalId . '-invalidated',
                'debtor_data_hash' => 'INVALID_HASH',
            ]);
        } else {
            $this->logInfo('[Dry Run] Skip the SQL execution.');
        }
    }

    private function transformExternalData(array $data)
    {
        $city = $this->exploreCity($data['plz']);
        list($street, $houseNumber) = $this->exploreStreet($data['address']);
        $legalForm = $this->exploreLegalForm($data['company_name']);
        list($firstName, $restName) = $this->exploreName($data['customer_name']);

        return [
            'company_name' => $data['company_name'],
            'person_first_name' => $firstName,
            'person_last_name' => $restName,
            'address_country' => 'DE',
            'address_city' => $city,
            'address_postal_code' => $data['plz'],
            'address_street' => $street,
            'address_house' => $houseNumber,
            'legal_form' => $legalForm,

            'person_email' => null,
            'person_phone' => null,
            'address_addition' => null,
            'billing_address_country' => 'DE',
            'billing_address_city' => $city,
            'billing_address_postal_code' => $data['plz'],
            'billing_address_street' => $street,
            'billing_address_house' => $houseNumber,
            'billing_address_addition' => null,
            'tax_id' => null,
            'tax_number' => null,
            'registration_court' => null,
            'registration_number' => null,
            'industry_sector' => null,
            'subindustry_sector' => null,
            'employees_number' => null,
        ];
    }

    private function exploreStreet(string $street): array
    {
        $chunks = explode(' ', $street);
        if (count($chunks) === 1) {
            return [$street, null];
        }

        $houseNumber = array_pop($chunks);

        return [
            implode(' ', $chunks),
            $houseNumber,
        ];
    }

    private function exploreName(string $name): array
    {
        $chunks = explode(' ', $name);
        if (count($chunks) === 1) {
            return [$name, null];
        }

        $firstName = array_shift($chunks);

        return [
            $firstName,
            implode(' ', $chunks),
        ];
    }

    private function exploreLegalForm(string $companyName): string
    {
        foreach (self::$legalForms as $short => $long) {
            $companyName = strtolower($companyName);

            if (
                strpos($companyName, strtolower($short)) !== false
                || strpos($companyName, strtolower($long)) !== false
            ) {
                return $short;
            }
        }

        return '-';
    }

    private function exploreCity(string $postIndex): string
    {
        static $dictionary = null;
        if ($dictionary === null) {
            $file = __DIR__ . '/../../Resources/german-zip-codes.csv';
            $contents = array_map(fn ($line) => str_getcsv($line, ';'), file($file));
            for ($i = 1, $j = count($contents); $i < $j; $i++) {
                $dictionary[$contents[$i][2]] = $contents[$i][0];
            }
        }

        if (array_key_exists($postIndex, $dictionary)) {
            return $dictionary[$postIndex];
        }

        return '-';
    }
}
