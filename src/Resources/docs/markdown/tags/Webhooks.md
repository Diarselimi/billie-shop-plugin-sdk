To send updates on relevant events, Billie PaD sends POST requests to the incoming webhook URL provided by the merchant.
See "Event Names" section for the list of events that can be communicated via webhooks

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
| reminder_email_2 | 2nd Reminder mail was sent               |
| dunning_letter_1 | 1st Dunning letter was sent              |
| dunning_email_1  | 1st Dunning mail was sent                |
| reminder_email_3 | 3rd Reminder email was sent              |
| dunning_letter_2 | 2nd Dunning letter was sent              |
| dunning_email_2  | 2nd Dunning email was sent               |
| reminder_email_4 | 4th Reminder email was sent              |
| dca_email        | Handover to collections email was sent   |


