Feature:
  In order to retrieve the order payments
  I want to call the get order payments endpoint

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "Authorization" header equal to "Bearer someToken"
    And a merchant user exists with permissions:
      | permission |
      | VIEW_ORDERS |
      | SHIP_ORDERS |
    And I get from Oauth service a valid user token

  Scenario: Successful invoice payments retrieval
    Given I have a shipped order "XF43Y" with amounts 100/90/10, duration 30 and comment "test order"
    And the following invoice data exists:
      | order_id | invoice_uuid                         |
      | 1        | 208cfe7d-046f-4162-b175-748942d6cff4 |
    And I get from invoice-butler service good response
    And invoice-butler call to "/invoices" will respond with 200 and response:
    """
    [
      {
        "uuid": "208cfe7d-046f-4162-b175-748942d6cff4",
        "payment_uuid": "13ba0778-9767-4971-b98d-a83a8164a4a7",
        "external_code": "some_code",
        "invoice_number": "INV-123",
        "offered_amount": 100.00,
        "amount": 100.00,
        "amount_net": 90.00,
        "created_at": "2020-10-12 12:12:12",
        "billing_date": "2020-12-01 12:12:12",
        "due_date": "2020-12-26 12:12:12",
        "payout_date": "2021-01-01 10:10:10",
        "state": "new",
        "duration": 30,
        "payout_amount": 0.00,
        "fee_amount": 0.00,
        "fee_net_amount": 0.00,
        "fee_vat_amount": 0.00,
        "factoring_fee_rate": 0,
        "deferral_fee_rate": 0,
        "discount_rate": 0,
        "outstanding_amount": 500,
        "invoice_discount_rate": 0,
        "company_discount_rate": 0,
        "proof_of_delivery_url": "http://foobar.io"
      }
    ]
    """
    And GraphQL will respond to getPadOrderPayments with 200 and response:
    """
    {
      "data": {
        "getPadOrderPayments": [
          {
            "created_at": "2018-06-25T17:10:05Z",
            "mapped_at": "2018-07-01T11:06:35Z",
            "mapped_amount": 22.45,
            "pending_amount": 0.01,
            "transaction_uuid": "34bad2f1-f36d-4985-a312-2203da7ef306",
            "payment_type": "merchant_payment",
            "debtor_name": "Dummy Merchant GmbH"
          },
          {
            "created_at": "2018-06-26T17:10:05Z",
            "mapped_at": "2018-07-02T11:06:35Z",
            "mapped_amount": 2.00,
            "pending_amount": 0.01,
            "transaction_uuid": "7875a655-3263-435f-a5a6-9b0bea42e90a",
            "payment_type": "merchant_payment",
            "debtor_name": "Dummy Merchant GmbH"
          },
          {
            "created_at": "2018-06-28T17:10:05Z",
            "mapped_at": "2018-07-11T11:06:35Z",
            "mapped_amount": 47.55,
            "pending_amount": 0.02,
            "transaction_uuid": "fc23cb4e-77c3-11e9-a2c4-02c6850949d6",
            "payment_type": "invoice_payback",
            "debtor_name": "Dummy Debtor GmbH"
          },
          {
            "created_at": "2018-06-29T17:10:05Z",
            "mapped_at": "2018-07-12T11:06:35Z",
            "mapped_amount": 5.00,
            "pending_amount": 0.02,
            "transaction_uuid": "17eca8da-e9c0-4853-8597-fe756c0f2576",
            "payment_type": "invoice_payback",
            "debtor_name": "Dummy Debtor GmbH"
          },
          {
            "created_at": "2018-06-28T17:10:05Z",
            "mapped_at": null,
            "mapped_amount": 1.25,
            "pending_amount": 0.03,
            "transaction_uuid": "9cda01dd-1189-4d15-92f9-ef9662292d3f",
            "payment_type": "invoice_cancellation",
            "debtor_name": "Dummy Debtor GmbH"
          },
          {
            "created_at": "2018-06-28T17:10:05Z",
            "mapped_at": "2018-06-28T17:10:05Z",
            "mapped_amount": 5.88,
            "pending_amount": 0.04,
            "transaction_uuid": "19b5919b-efb6-4bed-b551-2481aede7ed8",
            "payment_type": "invoice_cancellation",
            "debtor_name": "Dummy Debtor GmbH"
          }
        ]
      }
    }
    """
    When I send a GET request to "/invoices/208cfe7d-046f-4162-b175-748942d6cff4/payments"
    Then the response status code should be 200
    And the JSON response should be:
    """
    {
      "summary": {
        "merchant_paid_amount": 24.45,
        "debtor_paid_amount": 52.55,
        "merchant_unmapped_amount": 0.0,
        "debtor_unmapped_amount": 0.0,
        "total_paid_amount": 77,
        "cancelled_amount": 5.88,
        "open_amount": 23
      },
      "items": [
        {
          "transaction_uuid": "34bad2f1-f36d-4985-a312-2203da7ef306",
          "type": "merchant_payment",
          "state": "complete",
          "amount": 22.45,
          "debtor_name": "Dummy Merchant GmbH",
          "created_at": "2018-07-01 11:06:35"
        },
        {
          "transaction_uuid": "7875a655-3263-435f-a5a6-9b0bea42e90a",
          "type": "merchant_payment",
          "state": "complete",
          "amount": 2,
          "debtor_name": "Dummy Merchant GmbH",
          "created_at": "2018-07-02 11:06:35"
        },
        {
          "transaction_uuid": "fc23cb4e-77c3-11e9-a2c4-02c6850949d6",
          "type": "invoice_payback",
          "state": "complete",
          "amount": 47.55,
          "debtor_name": "Dummy Debtor GmbH",
          "created_at": "2018-07-11 11:06:35"
        },
        {
          "transaction_uuid": "17eca8da-e9c0-4853-8597-fe756c0f2576",
          "type": "invoice_payback",
          "state": "complete",
          "amount": 5,
          "debtor_name": "Dummy Debtor GmbH",
          "created_at": "2018-07-12 11:06:35"
        },
        {
          "transaction_uuid": "9cda01dd-1189-4d15-92f9-ef9662292d3f",
          "type": "invoice_cancellation",
          "state": "new",
          "amount": 1.25,
          "debtor_name": "Dummy Debtor GmbH",
          "created_at": "2018-06-28 17:10:05"
        },
        {
          "transaction_uuid": "19b5919b-efb6-4bed-b551-2481aede7ed8",
          "type": "invoice_cancellation",
          "state": "complete",
          "amount": 5.88,
          "debtor_name": "Dummy Debtor GmbH",
          "created_at": "2018-06-28 17:10:05"
        }
      ],
      "total": 6
    }
    """
