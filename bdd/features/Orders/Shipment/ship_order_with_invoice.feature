Feature:
  In order to ship an order with uploaded invoice file
  I want to have an end point to ship my orders and upload invoice file

  Background:
    Given I add "X-Test" header equal to 1
    And The following notification settings exist for merchant 1:
      | notification_type | enabled |
      | order_shipped     | 1       |
    And I get from payments service create ticket response
    And I get from companies service identify match response
    And GraphQL will respond to getMerchantDebtorDetails query
    And I get from files service a good response
    And a merchant user exists with permission SHIP_ORDERS
    And I get from Oauth service a valid user token
    And I add "Authorization" header equal to "Bearer someToken"

  Scenario: Successful invoice upload and order shipment
    Given I have a created order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
    When I send a POST request to "/order/test-order-uuidCO123/ship-with-invoice" with parameters:
      | key               | value              |
      | invoice_number    | 123456A            |
      | external_order_id | 1233XYZ            |
      | invoice_file      | @dummy-invoice.png |
    Then the JSON response should be:
    """
    {
      "order_id": "CO123",
      "uuid": "test-order-uuidCO123",
      "state": "shipped",
      "decline_reason": null,
      "reasons": null,
      "amount": 1000,
      "amount_net": 900,
      "amount_tax": 100,
      "unshipped_amount":0,
      "unshipped_amount_net":0,
      "unshipped_amount_tax":0,
      "duration": 30,
      "dunning_status": null,
      "debtor_company": {
        "name": "Test User Company",
        "address_house_number": "10",
        "address_street": "Heinrich-Heine-Platz",
        "address_postal_code": "10179",
        "address_city": "Berlin",
        "address_country": "DE"
      },
      "bank_account": {
        "iban": "DE1234",
        "bic": "BICISHERE"
      },
      "debtor_external_data": {
        "merchant_customer_id": "ext_id",
        "name": "test",
        "address_country": "TE",
        "address_city": "testCity",
        "address_postal_code": "test",
        "address_street": "test",
        "address_house": "test",
        "industry_sector": "test"
      },
      "delivery_address":{
        "house_number":"test",
        "street":"test",
        "city": "test",
        "postal_code":"test",
        "country":"TE"
      },
      "billing_address":{
        "house_number":"test",
        "street":"test",
        "city":"test",
        "postal_code":"test",
        "country":"TE"
      },
      "created_at":"2019-05-20T13:00:00+0200",
      "shipped_at":null,
      "debtor_uuid":null,
      "workflow_name":"order_v1",
      "invoices": [{
        "uuid": "57ecaca2-aaf5-4c36-b41c-cea57330cb45",
        "invoice_number": "123456A",
        "payout_amount": 762,
        "outstanding_amount": 1000,
        "amount": 1000,
        "amount_net": 900,
        "amount_tax": 100,
        "fee_amount": 238,
        "fee_rate": 20,
        "due_date": "2021-03-21",
        "created_at": "2021-02-19",
        "duration": 30,
        "state": "new",
        "pending_merchant_payment_amount": 0,
        "pending_cancellation_amount": 0
      }],
      "invoice": {
        "invoice_number": "123456A",
        "payout_amount": 1000,
        "outstanding_amount": 1000,
        "fee_amount": 238,
        "fee_rate": 20,
        "due_date": "2019-06-19",
        "pending_merchant_payment_amount": 0,
        "pending_cancellation_amount": 0
      }
    }
    """
    And the response status code should be 200
    And the order "CO123" is in state shipped
    And Order notification should exist for order "CO123" with type "order_shipped"
    And the order "CO123" has invoice data
    And queue should contain message with routing key invoice.create_invoice with below data:
    """
    {
      "uuid":"@string@",
      "externalCode":"123456A",
      "orderExternalCode":"CO123",
      "customerUuid":"f2ec4d5e-79f4-40d6-b411-31174b6519ac",
      "debtorCompanyUuid":"c7be46c0-e049-4312-b274-258ec5aeeb70",
      "debtorCompanyName":"Test User Company",
      "paymentDebtorUuid":"test",
      "grossAmount":"100000",
      "netAmount":"90000",
      "feeRate":2000,
      "grossFeeAmount":"23800",
      "netFeeAmount":"20000",
      "taxFeeAmount":"3800",
      "duration":30,
      "billingDate":"@string@",
      "services":["financing","dci"],
      "paymentUuid":"@string@"
    }
    """
    And the order "CO123" has a payment id

  Scenario: Ship order with invoice with amount (partial activation)
    Given I have orders with the following data
      | external_id | state   | gross | net | tax | duration | comment        | workflow_name |
      | CO123       | created | 1000  | 900 | 100 | 30       | "test order"   | order_v2      |
    And the order "CO123" does not have a payment id
    And I get from payments service get order details response
    When I send a POST request to "/order/test-order-uuidCO123/ship-with-invoice" with parameters:
      | key               | value                            |
      | invoice_number    | 123456A                          |
      | external_order_id | 1233XYZ                          |
      | invoice_file      | @dummy-invoice.png               |
      | amount            | {"gross":500,"net":450,"tax":50} |
    Then the JSON response should be:
    """
    {
      "order_id":"CO123",
      "uuid":"test-order-uuidCO123",
      "state":"partially_shipped",
      "decline_reason":null,
      "reasons":null,
      "amount":1000,
      "amount_net":900,
      "amount_tax":100,
      "unshipped_amount":500,
      "unshipped_amount_net":450,
      "unshipped_amount_tax":50,
      "duration":30,
      "dunning_status":null,
      "debtor_company":{
        "name":"Test User Company",
        "address_house_number":"10",
        "address_street":"Heinrich-Heine-Platz",
        "address_postal_code":"10179",
        "address_city":"Berlin",
        "address_country":"DE"
      },
      "bank_account":{
        "iban":"DE1234",
        "bic":"BICISHERE"
      },
      "debtor_external_data":{
        "merchant_customer_id":"ext_id",
        "name":"test",
        "address_country":"TE",
        "address_city":"testCity",
        "address_postal_code":"test",
        "address_street":"test",
        "address_house":"test",
        "industry_sector":"test"
      },
      "delivery_address":{
        "house_number":"test",
        "street":"test",
        "city":"test",
        "postal_code":"test",
        "country":"TE"
      },
      "billing_address":{
        "house_number":"test",
        "street":"test",
        "city":"test",
        "postal_code":"test",
        "country":"TE"
      },
      "debtor_uuid":null,
      "workflow_name":"order_v2",
      "invoices":[
        {
          "uuid":"68ccacff-8590-4b6a-8728-57b0748a07bf",
          "invoice_number":"123456A",
          "payout_amount":381,
          "outstanding_amount":500,
          "amount":500,
          "amount_net":450,
          "amount_tax":50,
          "fee_amount":119,
          "fee_rate":20,
          "due_date":"2021-03-19",
          "created_at":"2021-02-17",
          "duration":30,
          "state":"new",
          "pending_merchant_payment_amount":0,
          "pending_cancellation_amount":0
        }
      ],
      "invoice":{
        "due_date":"2019-06-19",
        "invoice_number":"123456A",
        "payout_amount":381,
        "outstanding_amount":500,
        "fee_amount":119,
        "fee_rate":20,
        "pending_merchant_payment_amount":0,
        "pending_cancellation_amount":0
      }
    }
    """
    And the response status code should be 200
    And the order "CO123" is in state partially_shipped
    And the order "CO123" has no invoice data
    And the order "CO123" has an invoice
    And queue should contain message with routing key invoice.create_invoice with below data:
    """
    {
      "uuid":"@string@",
      "externalCode":"123456A",
      "orderExternalCode":"CO123",
      "customerUuid":"f2ec4d5e-79f4-40d6-b411-31174b6519ac",
      "debtorCompanyUuid":"c7be46c0-e049-4312-b274-258ec5aeeb70",
      "debtorCompanyName":"Test User Company",
      "paymentDebtorUuid":"test",
      "grossAmount":"50000",
      "netAmount":"45000",
      "feeRate":2000,
      "grossFeeAmount":"11900",
      "netFeeAmount":"10000",
      "taxFeeAmount":"1900",
      "duration":30,
      "billingDate":"@string@",
      "services":["financing","dci"],
      "paymentUuid":"@string@"
    }
    """
    And the order "CO123" does not have a payment id
