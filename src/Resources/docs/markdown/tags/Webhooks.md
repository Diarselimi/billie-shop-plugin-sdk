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
    event: reminder|dunning1|dunning2|dunning3|payment|dca
    order_id: (string)
    amount: (string) (optional) (contains sum of paid amounts)
	open amount: (string) (optional) (contains sum of outstanding amount)
    url_notification: (string) (optional)
}
```
