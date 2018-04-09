<?php

use Phinx\Migration\AbstractMigration;

class InitialStructure extends AbstractMigration
{
    public function change()
    {
        $this
            ->table('customers')
            ->addColumn('name', 'string', ['null' => false])
            ->addColumn('available_financing_limit', 'float', ['null' => false])
            ->addColumn('api_key', 'string', ['null' => false])
            ->addColumn('roles', 'string', ['null' => false])
            ->addColumn('is_active', 'boolean', ['null' => false])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addColumn('updated_at', 'datetime', ['null' => false])
            ->addIndex('api_key', ['unique' => true])
            ->create()
        ;

        $this
            ->table('addresses')
            ->addColumn('country', 'string', ['null' => false])
            ->addColumn('city', 'string', ['null' => false])
            ->addColumn('postal_code', 'string', ['null' => false])
            ->addColumn('street', 'string', ['null' => false])
            ->addColumn('house', 'string', ['null' => false])
            ->addColumn('addition', 'string', ['null' => true])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addColumn('updated_at', 'datetime', ['null' => false])
            ->create()
        ;

        $this
            ->table('persons')
            ->addColumn('gender', 'char', ['null' => false, 'limit' => 1])
            ->addColumn('first_name', 'string', ['null' => false])
            ->addColumn('last_name', 'string', ['null' => false])
            ->addColumn('email', 'string', ['null' => false])
            ->addColumn('phone', 'string', ['null' => false])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addColumn('updated_at', 'datetime', ['null' => false])
            ->create()
        ;

        $this
            ->table('debtor_external_data')
            ->addColumn('address_id', 'integer', ['null' => false])
            ->addColumn('name', 'string', ['null' => false])
            ->addColumn('legal_form', 'string', ['null' => false])
            ->addColumn('tax_id', 'string', ['null' => true])
            ->addColumn('tax_number', 'string', ['null' => true])
            ->addColumn('registration_court', 'string', ['null' => true])
            ->addColumn('registration_number', 'string', ['null' => true])
            ->addColumn('industry_sector', 'string', ['null' => false])
            ->addColumn('subindustry_sector', 'string', ['null' => false])
            ->addColumn('employees_number', 'string', ['null' => true])
            ->addColumn('is_established_customer', 'boolean', ['null' => false])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addColumn('updated_at', 'datetime', ['null' => false])
            ->create()
        ;

        $this
            ->table('companies')
            ->addColumn('debtor_id', 'string', ['null' => false])
            ->addColumn('merchant_id', 'string', ['null' => false])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addColumn('updated_at', 'datetime', ['null' => false])
            ->create()
        ;

        $this
            ->table('orders')
            ->addColumn('amount_net', 'float', ['null' => false])
            ->addColumn('amount_gross', 'float', ['null' => false])
            ->addColumn('amount_tax', 'float', ['null' => false])
            ->addColumn('duration', 'integer', ['null' => false])
            ->addColumn('external_code', 'string', ['null' => false])
            ->addColumn('state', 'string', ['null' => false])
            ->addColumn('external_comment', 'string', ['null' => true])
            ->addColumn('internal_comment', 'string', ['null' => true])
            ->addColumn('invoice_number', 'string', ['null' => true])
            ->addColumn('invoice_url', 'string', ['null' => true])
            ->addColumn('customer_id', 'integer', ['null' => false])
            ->addColumn('company_id', 'integer', ['null' => true])
            ->addColumn('delivery_address_id', 'integer', ['null' => false])
            ->addColumn('debtor_person_id', 'integer', ['null' => false])
            ->addColumn('debtor_external_data_id', 'integer', ['null' => false])
            ->addColumn('payment_id', 'char', ['null' => true, 'limit' => 36])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addColumn('updated_at', 'datetime', ['null' => false])
            ->addForeignKey('customer_id', 'customers', 'id')
            ->addForeignKey('company_id', 'companies', 'id')
            ->addForeignKey('delivery_address_id', 'addresses', 'id')
            ->addForeignKey('debtor_person_id', 'persons', 'id')
            ->addForeignKey('debtor_external_data_id', 'debtor_external_data', 'id')
            ->create()
        ;
    }
}
