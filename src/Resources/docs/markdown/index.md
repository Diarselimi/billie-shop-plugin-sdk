# Introduction

The PaD API provides an easy way to integrate both client and server-side 
with our __Billie Pay After Delivery (PaD)__ platform.

_Billie PaD_ is a Business to Business (B2B) platform that enables you (the merchant) to accept invoice payments from
other business customers without having to wait for the money to arrive, caring about reminding customers to pay,
or taking over any credit risk.

Based upon your contractual agreement with Billie, your customers will have 14, 30, 45, 60, 90 or 120 days (payment terms) 
to pay your invoices while Billie will transfer the money to you immediately after shipment of product or fulfilment of
service obligation.

If customers get late with paying their outstanding invoices, Billie will also take over reminding customers on your behalf 
(white label solution). In case of your customer's bankruptcy, Billie fully covers this default for you.

-----
## Authentication
For authentication and authorisation the API requires some requests to include valid API credentials via HTTP headers.
Different keys are used for the `sandbox` and `production` environments, which are completely independent
meaning that they cannot access or alter data from the other.

To obtain your credentials for both environments please contact us or send an email to: pad@billie.io.

## OAuth 2.0
The API uses OAuth 2.0 for authentication.

This authentication mechanism is based on the OAuth 2.0 prototocol and requires a Client ID, a Client Secret Key and
in some flows also a user (email) and password.

With these credentials, the client should request a token using the [oauth/token](#operation/oauth_token_create) endpoint,
which should be used in the `Authorization` header together with the `Bearer` challenge to authenticate all the requests.

> Example: `Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c`.

---

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


----

## Incoming Webhooks
To send updates on relevant events, Billie PaD sends POST requests to the incoming webhook URL provided by the merchant.
The following events can be communicated via webhooks:


### Webhook Authentication
The authentication for webhooks sent from Billie to the merchants incoming webhook URL depends on the merchant specifications 
and needs, but one possibility is sending the Billie API Key via headers:
Examples:
 - `Authorization: Basic THE_API_KEY`
 - `X-Api-Key: THE_API_KEY`
 - `X-Another-Custom-Header: THE_API_KEY`

The merchants will need to verify on their server side the authentication mechanism and credentials.

### Webhook Requests

A `POST` request is sent to the configured merchant Webhook URL with one of the API Key headers mentioned above,
the `application/json` Content-Type and the following body format:
```
{
    event: (string) (event name, -check the following section for more details-)
    order_id: (string)
    amount: (string) (optional) (contains sum of paid amounts)
	open amount: (string) (optional) (contains sum of outstanding amount)
    url_notification: (string) (optional)
}
```

### Event Names

**General**

| Event Name      | Description                                                                                                                |
|-----------------|----------------------------------------------------------------------------------------------------------------------------|
| order\_approved | waiting order approved                                                                                                     |
| order\_declined | waiting order declined                                                                                                     |
| payment         | The outstanding amount for an order changed. <br><br> This message is sent for full as well as partial payments for orders |

<br>

**Webhooks for Dunning & Collections**

| Event Name       | Description                              |
|------------------|------------------------------------------|
| reminder_email_1 | 1st Reminder mail was sent               |
| dunning_letter_1 | 1st Dunning letter was sent              |
| dunning_email_1  | 1st Dunning mail was sent                |
| reminder_email_2 | 2nd Reminder mail was sent               |
| dunning_letter_2 | 2nd Dunning letter was sent              |
| reminder_email_2 | 2nd Dunning email was sent               |
| dunning_email_2  | 2nd Dunning email was sent               |
| reminder_email_3 | 3rd Reminder email was sent              |
| dca_letter       | Debt collection handover letter was sent |
| dca_email        | Debt collection handover email was sent  |


