<?php

declare(strict_types=1);

namespace App\Tests\Helpers\Behat;

use App\DomainModel\BankAccount\BankAccountServiceInterface;
use Ozean12\BancoSDK\Model\Bank;

/**
 * This class exists because the Banco SDK cannot handle JSON mocked calls properly.
 * https://github.com/ozean12/banco-sdk-php/blob/ea7841865e643d5587a2f611ec325a65b46d6f27/lib/ObjectSerializer.php#L276-L279
 * If you use an associative JSON array, this will fail because json_decode() will return instances of stdClass
 * by default instead of an array.
 *
 * The only alternative to using this fake client is to set a custom request handler for the Guzzle client,
 * feel free to refactor this to do so.
 */
class FakeBankAccountService implements BankAccountServiceInterface
{
    public function getBankByBic(string $bic): Bank
    {
        return new Bank([
            'bic' => $bic,
            'name' => 'Mocked Bank Name GmbH',
        ]);
    }
}
