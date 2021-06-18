Create an invoice for the order. It can be either a partial activation or a full activation.

> Example: If you plan to ship multiple items of an order from different warehouses at different dates to one client, you can create an invoice for each of these partial shipments by specifying the amount of each shipment (invoice).

Please specify the order which is being shipped in the `orders` property. Collective invoices are not yet supported, so only one order uuid or external code can be provided.

