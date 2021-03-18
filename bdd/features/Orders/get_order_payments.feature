Feature:
  In order to retrieve the order payments
  I want to call the get order payments endpoint

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "Authorization" header equal to "Bearer someToken"
    And I get from Oauth service a valid user token
    And a merchant user exists with permission VIEW_ORDERS

  Scenario: Successful order payments retrieve
    Given I have orders with the following data
      | external_id | state      | gross | net | tax | duration | comment    | payment_uuid                         |
      | XF43Y       | new        | 1000  | 900 | 100 | 30       | test order | 6d6b4222-be8c-11e9-9cb5-2a2ae2dbcce4 |
    And GraphQL will respond to getPadOrderPayments with 200 and response:
    """
    {
      "data": {
        "getPadOrderPayments": [
          {
            "created_at": "2018-06-28T17:10:05Z",
            "mapped_at": "2018-07-11T11:06:35Z",
            "mapped_amount": 67.12,
            "pending_amount": 67.12,
            "transaction_uuid": "fc23cb4e-77c3-11e9-a2c4-02c6850949d6",
            "payment_type": "invoice_payback",
            "debtor_name": "Dummy Debtor GmbH"
          },
          {
            "created_at": "2018-06-28T17:10:05Z",
            "mapped_at": null,
            "mapped_amount": null,
            "pending_amount": 67.12,
            "transaction_uuid": null,
            "payment_type": "invoice_payback",
            "debtor_name": null
          }
        ]
      }
    }
    """
    When I send a GET request to "/order/test-order-uuidXF43Y/payments"
    Then the response status code should be 200
    And the JSON response should be:
    """
    {
      "items": [
        {
          "created_at": "2018-06-28T17:10:05",
          "amount": 67.12,
          "type": "invoice_payback",
          "state": "complete",
          "transaction_uuid": "fc23cb4e-77c3-11e9-a2c4-02c6850949d6",
          "debtor_name": "Dummy Debtor GmbH"
        }
      ],
      "total": 1
    }
    """
