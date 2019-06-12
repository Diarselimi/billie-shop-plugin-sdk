# Introduction

Billie PayAfterDelivery (PaD) offers an easy to use and easy to integrate client and server-side 
APIs based on our Pay After Delivery platform.

Billie enables you (referenced as the "merchant" from now on) to accept invoice payments from business customers (B2B)
without having to wait for the money to arrive, caring about reminding customers to pay, or taking over any credit risk.

Based upon your contractual agreement with Billie, your customers will have 14, 30, 45, 60, 90 or 120 days (payment terms) 
to pay your invoices while Billie will transfer the money to you immediately after shipment of product or fulfilment of service obligation.

If customers get late with paying their outstanding invoices, Billie will also take over reminding customers on your behalf 
(white label solution). In case of your customer's bankruptcy Billie fully covers this default for you.


# API Access
The API is based on REST and uses `application/json` as requests and responses Content Type. 

The API base URLs are:

- Production: https://paella.billie.io/api/v1
- Sandbox: https://paella-sandbox.billie.io/api/v1


# Authentication

For authentication and authorisation the API requires requests to include valid API credentials via HTTP headers.
Different keys are used for the `sandbox` and `production` environments, which are completely independent
meaning that they cannot access or alter data from the other.

To obtain your credentials for both environments please send an email to: pad@billie.io.


# Incoming Webhooks
To send updates on relevant events, Billie PaD sends POST requests to the incoming webhook URL provided by the merchant.
The following events can be communicated via webhooks:


## Webhook Authentication
The authentication for webhooks sent from Billie to the merchants incoming webhook URL depends on the merchant specifications 
and needs, but one possibility is sending the Billie API Key via headers:
Examples:
 - `Authorization: Basic THE_API_KEY`
 - `X-Api-Key: THE_API_KEY`
 - `X-Another-Custom-Header: THE_API_KEY`

The merchants will need to verify on their server side the authentication mechanism and credentials.

## Webhook Requests

A `POST` request is sent to the configured merchant Webhook URL with one of the API Key headers mentioned above,
the `application/json` Content-Type and the following body format:
```
{
    event: reminder|dunning1|dunning2|dunning3|payment|dca
    order_id: (string)
    amount: (string) (optional) (contains sum of paid amounts)
	open amount: (string) (optional) (contains sum of outstanding amount)
    url_notification: (string) (optional)
}
```
