Feature: Get invoice from invoice-butler

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "Authorization" header equal to "Bearer someToken"
    And I get from Oauth service a valid user token
    And a merchant user exists with permission VIEW_ORDERS

  Scenario: Get an invoice successfully
    Given I have a new order "ABCDE" with amounts 1000/900/100, duration 30 and checkout session "208cfe7d-046f-4162-b175-748942d6cff2"
    And I get from invoice-butler service good response
    And the following invoice data exists:
      | order_id | invoice_uuid                         |
      | 1        | 208cfe7d-046f-4162-b175-748942d6cff4 |
    When I send a GET request to "/invoices/208cfe7d-046f-4162-b175-748942d6cff4"
    Then the response status code should be 200
    And the JSON response should be:
    """
    {
        "pending_merchant_payment_amount": 0,
        "due_date": "2020-12-26",
        "duration": 30,
        "created_at": "2020-10-12",
        "fee_rate": 20,
        "state": "new",
        "outstanding_amount": 500,
        "amount_tax": 0,
        "amount": 123.33,
        "pending_cancellation_amount": 0,
        "amount_net": 123.33,
        "fee_amount": 123.33,
        "orders": [
            {
                "uuid": "test-order-uuidABCDE",
                "external_code": "ABCDE",
                "amount": 1000,
                "amount_net": 900,
                "amount_tax": 100
            }
        ],
        "invoice_number": "some_code",
        "payout_amount": 123.33
    }
    """
