## Order States

| State       | Description                                                                                   |
|-------------|-----------------------------------------------------------------------------------------------|
| waiting     | The order creation failed and needs a manual operation in order to be approved or declined    |
| authorized  | The order is valid but it needs a confirmation from the merchant in order to be approved      |
| created     | The order is successfully approved and Billie can offer financing for this transaction        |
| declined    | The order was declined and no financing is offered for this transaction                       |
| shipped     | The order was successfully shipped by the merchant                                            |
| paid_out    | The outstanding amount for the respective order was successfully paid out to the merchant     |
| late        | The payment of the outstanding amount is overdue                                              |
| complete    | The outstanding amount was successfully paid back by the customer                             |
| canceled    | The order was canceled by the merchant       

## Order State Transitions

![img](src/Resources/docs/orders-workflow-public.png)

## Order Decline Reasons

An order could be declined by any of these reasons:

| Reason                | Description                                               |
|-----------------------|-----------------------------------------------------------|
| debtor_address        | Debtor address or name mismatch                           |
| debtor_not_identified | Debtor could not be identified with the information given |
| risk_policy           | Risk decline                                              |
| debtor_limit_exceeded | Financing limit of debtor currently exceeded              |


## Usage of Virtual IBANs

Since the money gets paid out to the merchant directly after shipment of the product or delivery of the agreed service, 
customers need to transfer the money directly to Billie when their invoice is due. Therefore Billie is using virtual IBANs 
that are delivered back to the merchant as an answer of a API order creation request. Virtual IBANs are unique for each of 
your debtor / customer. The virtual IBAN needs to be put on the invoice which the customer receives for the purchase.
