To send updates on relevant events, Billie PAD sends POST requests to the incoming webhook URL provided by the merchant.
See "Event Names" section for the list of events that can be communicated via webhooks.

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
    created_at: (datetime)
    event: (string) (event name, -check the following section for more details-)
    order_id: (string)
    order_uuid: (string)
    invoice_uuid: (string)
    amount: (string) (optional) (contains sum of paid amounts)
    open_amount: (string) (optional) (contains sum of outstanding amount)
}
```

## Event Names

### Order State Transitions

| Event Name    | Description |
|---------------|-------------|
|order_waiting  | Order is not approved waiting for manual confirmation.
|order_shipped  | Order is shipped.
|order_canceled | When the order is canceled.

### Invoice State Transitions
| Event Name       | Description                              
|------------------|------------------------------------------
| invoice_paid_out |  When the invoice is paid out             
| invoice_late     |  When the invoice payback is late
| invoice_canceled |  When the invoice is canceled

### Waiting State

| Event Name      | Description                                                                                                                |
|-----------------|----------------------------------------------------------------------------------------------------------------------------|
| order\_approved | An order that was in waiting state and after manual check has been approved.                                               
| order\_declined | An order that was in waiting state and after manual check has been declined.                                             

### Webhooks for Dunning & Collections

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

### Payments

| Event Name |  Description |
|------------|--------------|
| payment    | When an Invoice is paid back partially or fully a message will be sent.
