# Introduction

With PayAfterDelivery (PAD) / "Rechnungskauf" Billie offers a convenient, secure and simple way to allow your
B2B-customers to pay on invoice. Our solution enables merchants to accept invoice payments from other business customers
without waiting for their money, reminding customers about the payment, or assuming credit risk.

The following documentation describes our RESTFul API, which allows easy integration into your web-shop, eCommerce
platform or invoicing back-end.

Based upon your Billie contract, your customers will have between 7 and 120 days (payment term) to pay your invoices,
while Billie will transfer the money to you immediately after shipment of goods or fulfilment of service obligation. For
outstanding payments, Billie takes over reminding customers on your behalf (white-label solution). Additionally, Billie
protects you from credit risks by absorbing any potential debtor defaults.

---

## Order State Transitions

![img](src/Resources/docs/orders-workflow-public-v2.png)

## Order States

| State             | Description                                                                                   |
|---------------    |-----------------------------------------------------------------------------------------------|
| waiting           | The order creation failed and needs a manual operation in order to be approved or declined    |
| created           | The order is successfully approved and Billie can offer financing for this transaction        |
| declined          | The order was declined and no financing is offered for this transaction                       |
| partially_shipped | The order was partially shipped by the merchant
| shipped           | The order was successfully shipped by the merchant                                            |
| complete          | The outstanding amount was successfully paid back by the customer                             |
| canceled          | The order was canceled by the merchant
| pre_waiting       | The order was created via the checkout widget and needs manual operation.
| authorized        | State of an order after creating it via the checkout widget. For approval, it needs merchant-side confirmation.    |

## Invoice States

| State     | Description
|-----------|---------------
| New       | When new invoice is created.
| Paid Out  | The amount for the respective invoice was successfully paid out to the merchant.
| Late      | When the invoice is not paid back on time.
| Canceled  | When the invoice was canceled by the merchant.
| Complete  | The outstanding amount was successfully paid back by the customer.

## Order Decline Reasons

An order could be declined by any of these reasons:

| Reason                | Description                                               |
|-----------------------|-----------------------------------------------------------|
| debtor_address        | Debtor address or name mismatch                           |
| debtor_not_identified | Debtor could not be identified with the information given |
| risk_policy           | Risk decline                                              |
| risk_scoring_failed   | Not enough data available to make a decision              |
| debtor_limit_exceeded | Financing limit of debtor currently exceeded              |

## Usage of Virtual IBANs

Since payouts by Billie to the merchant are made immediately after shipment of goods or delivery of the agreed service,
customers need to pay back their invoices directly to Billie upon due date. Therefore, Billie is using so-called virtual
IBANs (VIBANs). VIBANs are delivered back to the merchant as response to an (accepted) order creation request. VIBANs
are unique for each of your debtor / customer. The VIBAN needs to be put on the invoice which the customer receives for
the purchase.
