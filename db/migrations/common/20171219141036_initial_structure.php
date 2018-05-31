<?php

use Phinx\Migration\AbstractMigration;

class InitialStructure extends AbstractMigration
{
    public function change()
    {
        $this
            ->table('merchants')
            ->addColumn('name', 'string', ['null' => false])
            ->addColumn('available_financing_limit', 'decimal', ['null' => false, 'precision' => 20, 'scale' => 2])
            ->addColumn('api_key', 'string', ['null' => false])
            ->addColumn('roles', 'string', ['null' => false])
            ->addColumn('is_active', 'boolean', ['null' => false])
            ->addColumn('company_id', 'string', ['null' => false])
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
            ->addColumn('subindustry_sector', 'string', ['null' => true])
            ->addColumn('employees_number', 'string', ['null' => true])
            ->addColumn('is_established_customer', 'boolean', ['null' => true])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addColumn('updated_at', 'datetime', ['null' => false])
            ->addForeignKey('address_id', 'addresses', 'id')
            ->create()
        ;

        $this
            ->table('merchants_debtors')
            ->addColumn('debtor_id', 'string', ['null' => false])
            ->addColumn('merchant_id', 'integer', ['null' => false])
            ->addColumn('external_id', 'string', ['null' => false])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addColumn('updated_at', 'datetime', ['null' => false])
            ->addForeignKey('merchant_id', 'merchants', 'id')
            ->create()
        ;

        $this
            ->table('orders')
            ->addColumn('amount_net', 'decimal', ['null' => false, 'precision' => 12, 'scale' => 2])
            ->addColumn('amount_gross', 'decimal', ['null' => false, 'precision' => 12, 'scale' => 2])
            ->addColumn('amount_tax', 'decimal', ['null' => false, 'precision' => 12, 'scale' => 2])
            ->addColumn('duration', 'integer', ['null' => false])
            ->addColumn('external_code', 'string', ['null' => false])
            ->addColumn('state', 'string', ['null' => false])
            ->addColumn('external_comment', 'string', ['null' => true])
            ->addColumn('internal_comment', 'string', ['null' => true])
            ->addColumn('invoice_number', 'string', ['null' => true])
            ->addColumn('invoice_url', 'string', ['null' => true])
            ->addColumn('merchant_debtor_id', 'integer', ['null' => true])
            ->addColumn('merchant_id', 'integer', ['null' => true])
            ->addColumn('delivery_address_id', 'integer', ['null' => false])
            ->addColumn('debtor_person_id', 'integer', ['null' => false])
            ->addColumn('debtor_external_data_id', 'integer', ['null' => false])
            ->addColumn('payment_id', 'char', ['null' => true, 'limit' => 36])
            ->addColumn('uuid', 'char', ['null' => false, 'limit' => 36])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addColumn('updated_at', 'datetime', ['null' => false])
            ->addForeignKey('merchant_debtor_id', 'merchants_debtors', 'id')
            ->addForeignKey('merchant_id', 'merchants', 'id')
            ->addForeignKey('delivery_address_id', 'addresses', 'id')
            ->addForeignKey('debtor_person_id', 'persons', 'id')
            ->addForeignKey('debtor_external_data_id', 'debtor_external_data', 'id')
            ->create()
        ;

        $this
            ->table('order_transitions')
            ->addColumn('transition', 'string', ['null' => false])
            ->addColumn('order_id', 'integer', ['null' => false])
            ->addColumn('transited_at', 'datetime', ['null' => false])
            ->addForeignKey('order_id', 'orders', 'id')
            ->create()
        ;

        $this
            ->table('risk_checks')
            ->addColumn('check_id', 'integer', ['null' => false])
            ->addColumn('order_id', 'integer', ['null' => false])
            ->addColumn('name', 'string', ['null' => false])
            ->addColumn('is_passed', 'string', ['null' => false])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addColumn('updated_at', 'datetime', ['null' => false])
            ->addForeignKey('order_id', 'orders', 'id')
            ->addIndex('check_id', ['unique' => true])
            ->create()
        ;
    }
}
