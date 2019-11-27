<?php

use App\Infrastructure\Phinx\TransactionalMigration;
use App\Infrastructure\Repository\MerchantOnboarding\MerchantOnboardingRepository;
use Ramsey\Uuid\Uuid;

class AddOnboardingStateForExistingMerchants extends TransactionalMigration
{
    public function migrate()
    {
        $merchants = $this->fetchAll('SELECT * FROM merchants');
        $now = (new DateTime())->format('Y-m-d H:i:s');
        $table = $this->table(MerchantOnboardingRepository::TABLE_NAME);

        foreach ($merchants as $merchant) {
            $table->insert([
                'uuid' => Uuid::uuid4(),
                'merchant_id' => $merchant['id'],
                'state' => 'complete',
                'created_at' => $merchant['created_at'],
                'updated_at' => $now,
            ])->save();
        }
    }
}
