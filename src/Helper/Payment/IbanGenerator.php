<?php

namespace App\Helper\Payment;

class IbanGenerator
{
    private const IBAN_FORMATS = [
        'AD' => [['n', 4],    ['n', 4],  ['c', 12]],
        'AE' => [['n', 3],    ['n', 16]],
        'AL' => [['n', 8],    ['c', 16]],
        'AT' => [['n', 5],    ['n', 11]],
        'AZ' => [['a', 4],    ['c', 20]],
        'BA' => [['n', 3],    ['n', 3],  ['n', 8],  ['n', 2]],
        'BE' => [['n', 3],    ['n', 7],  ['n', 2]],
        'BG' => [['a', 4],    ['n', 4],  ['n', 2],  ['c', 8]],
        'BH' => [['a', 4],    ['c', 14]],
        'BR' => [['n', 8],    ['n', 5],  ['n', 10], ['a', 1],  ['c', 1]],
        'CH' => [['n', 5],    ['c', 12]],
        'CR' => [['n', 3],    ['n', 14]],
        'CY' => [['n', 3],    ['n', 5],  ['c', 16]],
        'CZ' => [['n', 4],    ['n', 6],  ['n', 10]],
        'DE' => [['n', 8],    ['n', 10]],
        'DK' => [['n', 4],    ['n', 9],  ['n', 1]],
        'DO' => [['c', 4],    ['n', 20]],
        'EE' => [['n', 2],    ['n', 2],  ['n', 11], ['n', 1]],
        'ES' => [['n', 4],    ['n', 4],  ['n', 1],  ['n', 1],  ['n', 10]],
        'FI' => [['n', 6],    ['n', 7],  ['n', 1]],
        'FR' => [['n', 5],    ['n', 5],  ['c', 11], ['n', 2]],
        'GB' => [['a', 4],    ['n', 6],  ['n', 8]],
        'GE' => [['a', 2],    ['n', 16]],
        'GI' => [['a', 4],    ['c', 15]],
        'GR' => [['n', 3],    ['n', 4],  ['c', 16]],
        'GT' => [['c', 4],    ['c', 20]],
        'HR' => [['n', 7],    ['n', 10]],
        'HU' => [['n', 3],    ['n', 4],  ['n', 1],  ['n', 15], ['n', 1]],
        'IE' => [['a', 4],    ['n', 6],  ['n', 8]],
        'IL' => [['n', 3],    ['n', 3],  ['n', 13]],
        'IS' => [['n', 4],    ['n', 2],  ['n', 6],  ['n', 10]],
        'IT' => [['a', 1],    ['n', 5],  ['n', 5],  ['c', 12]],
        'KW' => [['a', 4],    ['n', 22]],
        'KZ' => [['n', 3],    ['c', 13]],
        'LB' => [['n', 4],    ['c', 20]],
        'LI' => [['n', 5],    ['c', 12]],
        'LT' => [['n', 5],    ['n', 11]],
        'LU' => [['n', 3],    ['c', 13]],
        'LV' => [['a', 4],    ['c', 13]],
        'MC' => [['n', 5],    ['n', 5],  ['c', 11], ['n', 2]],
        'MD' => [['c', 2],    ['c', 18]],
        'ME' => [['n', 3],    ['n', 13], ['n', 2]],
        'MK' => [['n', 3],    ['c', 10], ['n', 2]],
        'MR' => [['n', 5],    ['n', 5],  ['n', 11], ['n', 2]],
        'MT' => [['a', 4],    ['n', 5],  ['c', 18]],
        'MU' => [['a', 4],    ['n', 2],  ['n', 2],  ['n', 12], ['n', 3],  ['a', 3]],
        'NL' => [['a', 4],    ['n', 10]],
        'NO' => [['n', 4],    ['n', 6],  ['n', 1]],
        'PK' => [['a', 4],    ['c', 16]],
        'PL' => [['n', 8],    ['n', 16]],
        'PS' => [['a', 4],    ['c', 21]],
        'PT' => [['n', 4],    ['n', 4],  ['n', 11], ['n', 2]],
        'RO' => [['a', 4],    ['c', 16]],
        'RS' => [['n', 3],    ['n', 13], ['n', 2]],
        'SA' => [['n', 2],    ['c', 18]],
        'SE' => [['n', 3],    ['n', 16], ['n', 1]],
        'SI' => [['n', 5],    ['n', 8],  ['n', 2]],
        'SK' => [['n', 4],    ['n', 6],  ['n', 10]],
        'SM' => [['a', 1],    ['n', 5],  ['n', 5],  ['c', 12]],
        'TN' => [['n', 2],    ['n', 3],  ['n', 13], ['n', 2]],
        'TR' => [['n', 5],    ['n', 1],  ['c', 16]],
        'VG' => [['a', 4],    ['n', 16]],
    ];

    private const BIC = 'RANDOMBICXX';

    public function iban(string $countryCode, ?string $prefix = '', ?int $length = null): string
    {
        $countryCode = strtoupper($countryCode);
        $format = self::IBAN_FORMATS[$countryCode] ?? null;

        if ($length === null) {
            if ($format === null) {
                $length = 24;
            } else {
                $length = 0;
                foreach ($format as [$class, $groupCount]) {
                    $length += $groupCount;
                }
            }
        }

        if ($format === null) {
            $format = [['n', $length]];
        }

        $expandedFormat = '';

        foreach ($format as [$class, $formatLength]) {
            $expandedFormat .= str_repeat($class, $formatLength);
        }

        $result = $prefix;
        $expandedFormat = substr($expandedFormat, strlen($result));

        foreach (str_split($expandedFormat) as $class) {
            switch ($class) {
                default:
                case 'c':
                    $result .= random_int(0, 99) <= 50 ? random_int(0, 9) : strtoupper(chr(random_int(97, 121)));

                    break;
                case 'a':
                    $result .= strtoupper(chr(random_int(97, 121)));

                    break;
                case 'n':
                    $result .= random_int(0, 9);

                    break;
            }
        }

        $checksum = $this->checksum($countryCode . '00' . $result);

        return $countryCode . $checksum . $result;
    }

    public function bic(): string
    {
        return self::BIC;
    }

    public function alphaToNumberCallback(array $match): int
    {
        return ord($match[0]) - 55;
    }

    private function checksum(string $iban): string
    {
        $checkString = substr($iban, 4) . substr($iban, 0, 2) . '00';
        $checkString = preg_replace_callback('/[A-Z]/', [$this, 'alphaToNumberCallback'], $checkString);

        $checksum = (int) $checkString[0];
        for ($i = 1, $size = strlen($checkString); $i < $size; $i++) {
            $checksum = (10 * $checksum + (int) $checkString[$i]) % 97;
        }

        $checksum = 98 - $checksum;

        return str_pad($checksum, 2, '0', STR_PAD_LEFT);
    }
}
