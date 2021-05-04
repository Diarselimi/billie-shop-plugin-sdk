# Synchronize invoice command
The Synchronize invoice command is used to fill the webapp database with all the historical invoices from paella and update the already existing ones. The current document describes in details what happens during the execution of the command.

## Running the command
To run the command, execute the following instruction in the CLI:
```shell script
bin/console paella:sync-invoices --start-id=1 --limit=1000 --db-suffix=_santosh
```
, where:

`start-id` is the order id of the first paella order to process,

`limit` is the total number of orders to process
The command processes only the shipped orders, so if there will be some non-shipped orders in between, they will not count towards the limit.

`db-suffix`  is the database name suffix,when running on a test instance. Leave it empty for local or prod.

## The logic
### General words
The execution of the command is split into several steps:
- analyze database: run a select query and log the database statistics
- select the orders to process
- process each order in a separate transaction

The command will produce a lot of logs during the execution which would be useful to debug what happened or to keep in case anything will be messed up.

The following sections describe each step of the command in details.

### Database analyzis
Just a select query that will log the state of database. Example output:
```shell script
Total shipped orders: 281
Orders with invoices: 0
```
This would mean that there are 281 orders that were shipped and non of them have invoice in webapp.

### Selection of orders
The selection step executes the SQL query that queries all the orders where order id > `start-id` with limit of `limit` records. The query also selects order data from various sources (order_financial_details, borscht.tickets) that will be used in the next step.

### Processing the order
First, the command checks, if there is a record in the `order_invoices_v2` table (which would mean that the invoice in webapp was created). 

If the invoice exists, the update step will be executed, meaning:
- the following invoice data in webapp will be overriden with the actual data from the source of truth:  
```shell script
offered_amount 
amount
billing_date
due_date
duration 
payout_date 
payout_amount
factoring_fee_rate
outstanding_amount 
fee_amount
fee_vat_amount
fee_net_amount
net_amount
updated_at
```
- the financing workflow state and updated_at timestamp will be overriden

If the invoice doesn't exist, the new records will be inserted:
- new invoice record
- new financing workflow record
- new order_invoices_v2 record

After that, the command will insert all the missing documents into webapp.documents table and financing workflow transitions into the webapp.invoice_financing_workflow_transitions tables.

In case of any exception, the current order changes will be reverted and the command will stop.
