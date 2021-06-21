<?php

declare(strict_types=1);

use App\Infrastructure\Phinx\TransactionalMigration;
use App\Support\TwoWayEncryption\OpenSslEcbEncryption;

final class ChangeAlgorithmForEncryptedApiKey extends TransactionalMigration
{
    protected function migrate()
    {
        $key = getenv('ENCRYPTION_KEY');
        $encryptor = new OpenSslEcbEncryption($key);

        $merchants = $this->fetchAll('SELECT id, api_key, plain_api_key FROM merchants');
        $merchantIds = [];

        foreach ($merchants as $merchantData) {
            $merchantIds[] = $merchantData['id'];
            $hashedKey = $encryptor->encrypt($merchantData['plain_api_key']);
            $this->execute(
                'UPDATE `merchants` SET api_key = \'' . $hashedKey . '\' WHERE id = ' . $merchantData['id'] . ';'
            );
        }
    }
}
