Updates an order if changes are necessary. 
The following information can be updated until the order reached state complete:

- order amount: decrease the order amount (mostly due to partial cancellations of a purchase), always possible 
  unless the order is already fully paid.
- duration: extend timeframe for a payment. This changes the due date (§1).
- invoice details like invoice number and document URL.


§1 This call can also be used to move the due date into the future. This is possible from the moment the shipment is 
confirmed until the last day before reaching the current due date.

The new duration is to be set (i.e. initial request for 30 days, shipment on the 01.01., due date = 31.01., 
update duration on 45 days, new due date = 15.02.). 
