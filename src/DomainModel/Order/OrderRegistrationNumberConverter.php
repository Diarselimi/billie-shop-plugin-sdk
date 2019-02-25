<?php

namespace App\DomainModel\Order;

class OrderRegistrationNumberConverter
{
    private const LEGAL_FORMS_FOR_TYPE_B = [
        '11001, 11022', '11001', '11022', '10001, 10022', '10001', '10022', '14201', '14001', '10101, 10118, 10122', '10101', '10118', '10122', '91301', '10201',
    ];

    private const TYPE_A = 'HRA';

    private const TYPE_B = 'HRB';

    public function convert(string $registrationNumber, string $legalForm = ''): string
    {
        $registrationNumber = strtoupper($registrationNumber);

        if (!preg_match("/^([A-Za-z]*)\s*([0-9]+)\s*([A-Za-z]*)$/", $registrationNumber, $matches)) {
            return $registrationNumber;
        }

        if ($matches[1] === self::TYPE_A || $matches[1] === self::TYPE_B) {
            $newRegistrationNumber = $matches[1];
        } elseif (in_array($legalForm, self::LEGAL_FORMS_FOR_TYPE_B)) {
            $newRegistrationNumber = self::TYPE_B;
        } else {
            $newRegistrationNumber = self::TYPE_A;
        }

        $newRegistrationNumber .= " {$matches[2]}";

        if (!empty($matches[3])) {
            $newRegistrationNumber .= " {$matches[3]}";
        }

        return $newRegistrationNumber;
    }
}
