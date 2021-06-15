<?php

declare(strict_types=1);

use App\Infrastructure\Phinx\TransactionalMigration;
use App\Infrastructure\Repository\MerchantRepository;
use App\Support\TwoWayEncryption\EncryptorImpl;

final class AddEncryptedApiKeyColumn extends TransactionalMigration
{
    protected function migrate()
    {
        $tbl = $this->table(MerchantRepository::TABLE_NAME);
        $tbl->addColumn(
            'plain_api_key',
            'char',
            [
                'length' => 255,
                'null' => true,
                'after' => 'api_key',
            ]
        )->save();

        $key = getenv('ENCRYPTION_KEY');
        $encryptor = new EncryptorImpl($key);

        $sql = 'UPDATE merchants SET plain_api_key = api_key';
        $this->execute(
            $sql
        );

        $merchants = $this->fetchAll('SELECT id, api_key, plain_api_key FROM merchants');
        $merchantIds = [];

        foreach ($merchants as $merchantData) {
            $merchantIds[] = $merchantData['id'];
            $hashedKey = $encryptor->encrypt($merchantData['api_key']);
            $this->execute(
                'UPDATE `merchants` SET api_key = \'' . $hashedKey . '\' WHERE id = ' . $merchantData['id'] . ';'
            );
        }
    }
}
