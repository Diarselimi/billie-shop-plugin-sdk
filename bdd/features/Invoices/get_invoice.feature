Feature: Get invoice from invoice-butler

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "Authorization" header equal to "Bearer someToken"
    And I get from Oauth service a valid user token
    And a merchant user exists with permission VIEW_INVOICES

  Scenario: Get an invoice successfully
    Given I have a new order "ABCDE" with amounts 1000/900/100, duration 30 and checkout session "208cfe7d-046f-4162-b175-748942d6cff2"
    And I get from invoice-butler service good response
    And I get from invoice-butler payment methods response
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
        "amount":{"gross":123.33,"net":123.33,"tax":0},
        "pending_cancellation_amount": 0,
        "fee_amount": 123.33,
        "orders": [
            {
                "uuid": "test-order-uuidABCDE",
                "external_code": "ABCDE",
                "amount":{
                   "gross":1000,
                   "net":900,
                   "tax":100
                }
            }
        ],
        "invoice_number": "some_code",
        "payout_amount": 123.33,
        "credit_notes": [
          {
            "uuid": "208cfe7d-046f-4162-b175-748942d6cff5",
            "amount": {
              "gross": 22.0,
              "net": 20.0,
              "tax": 2.0
            },
            "external_code":  "some-code-CN",
            "comment": null,
            "created_at": "2021-03-01 12:12:12"
          },
          {
            "uuid": "208cfe7d-046f-4162-b175-748942d6cff6",
            "amount": {
              "gross": 33.0,
              "net": 30.0,
              "tax": 3.0
            },
            "external_code":  "another-code-CN",
            "comment": null,
            "created_at": "2021-03-01 12:12:12"
          }
        ],
        "payment_methods": [
          {
            "type":"bank_transfer",
            "data":{
              "bank_name":"Mocked Bank Name GmbH",
              "bic":"INGDDEFFXXX",
              "iban":"DE12500105179542622426"
            }
          }
        ]
    }
    """
