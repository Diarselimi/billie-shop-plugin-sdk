Feature:
  In order to ship an order with uploaded invoice file
  I want to have an end point to ship my orders and upload invoice file

  Background:
    Given I add "X-Test" header equal to 1
    And The following notification settings exist for merchant 1:
      | notification_type | enabled |
      | order_shipped     | 1       |

  Scenario: Successful invoice upload and order shipment
    Given I have a created order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
    And I get from payments service get order details not found response
    And I get from payments service create ticket response
    And I get from companies service identify match response
    And GraphQL will respond to getMerchantDebtorDetails query
    And I get from files service a good response
    And a merchant user exists with permission SHIP_ORDERS
    And I get from Oauth service a valid user token
    And I add "Authorization" header equal to "Bearer someToken"
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
      "reasons": null,
      "decline_reason": null,
      "amount": 1000,
      "amount_net": 900.00,
      "amount_tax": 100.00,
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
      "invoice": {
        "invoice_number": "123456A",
        "payout_amount": 1000,
        "outstanding_amount": 1000,
        "fee_amount": 10,
        "fee_rate": 1,
        "due_date": "1978-11-20",
        "pending_merchant_payment_amount": null,
        "pending_cancellation_amount": null
      },
      "debtor_external_data": {
        "name": "test",
        "address_country": "TE",
        "address_city": "testCity",
        "address_postal_code": "test",
        "address_street": "test",
        "address_house": "test",
        "industry_sector": "test",
        "merchant_customer_id":"ext_id"
      },
      "duration":30,
      "dunning_status": null,
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
      "debtor_uuid":null
    }
    """
    And the response status code should be 200
    And the order "CO123" is in state shipped
    And Order notification should exist for order "CO123" with type "order_shipped"
    And the order "CO123" has invoice data
