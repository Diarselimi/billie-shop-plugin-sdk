<?php

use App\Infrastructure\Repository\MerchantUserRepository;
use Phinx\Migration\AbstractMigration;

class AddFirstNameLastNameToMerchantUser extends AbstractMigration
{
    public function change()
    {
        $this
            ->table(MerchantUserRepository::TABLE_NAME)
            ->addColumn('first_name', 'string', ['null' => true, 'after' => 'merchant_id'])
            ->addColumn('last_name', 'string', ['null' => true, 'after' => 'first_name'])
            ->save()
        ;

        $this->setDefaultValues();
        $this
            ->table(MerchantUserRepository::TABLE_NAME)
            ->changeColumn('first_name', 'string', ['null' => false])
            ->changeColumn('last_name', 'string', ['null' => false])
            ->save();
    }

    private function setDefaultValues()
    {
        $sql = '
			UPDATE '.MerchantUserRepository::TABLE_NAME.' 
			SET `first_name` = "diar", 
				`last_name` = "sobaka"
		';
        $this->execute($sql);
    }
}
