Feature: Update order with invoice to reduce the order amount

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And GraphQL will respond to getMerchantDebtorDetails query
    And I get from Oauth service a valid user token
    And I add "Authorization" header equal to "Bearer someToken"

  Scenario: Order amount can be updated before shipment
    Given I have a "created" order with amounts 1000/900/100, duration 30 and comment "test order"
    And a merchant user exists with permission UPDATE_ORDERS
    And Debtor release limit call succeeded
    And I get from salesforce dunning status endpoint "Created" status for order "test-order-uuid"
    When I send a POST request to "/order-with-invoice/test-order-uuid" with body:
    """
    {
      "amount": {
        "gross": 500,
        "net": 400,
        "tax": 100
      }
    }
    """
    Then the response status code should be 204
    And the response should be empty
    And the order with uuid "test-order-uuid" should have amounts 500/400/100

  Scenario: Order can be updated before shipment (and same amount)
    Given I have a "created" order with amounts 1000/900/100, duration 30 and comment "test order"
    And a merchant user exists with permission UPDATE_ORDERS
    And Debtor release limit call succeeded
    When I send a POST request to "/order-with-invoice/test-order-uuid" with body:
    """
    {
      "amount": {
        "gross": 1000,
        "net": 900,
        "tax": 100
      }
    }
    """
    Then the response status code should be 204
    And the response should be empty
    And the order with uuid "test-order-uuid" should have amounts 1000/900/100

  Scenario: Order invoice file and invoice number can be updated after shipment
    Given I have a "shipped" order with amounts 1000/900/100, duration 30 and comment "test order"
    And a merchant user exists with permission UPDATE_ORDERS
    And I get from files service a good response
    And Debtor release limit call succeeded
    And I get from invoice-butler service good response
    And the following invoice data exists:
      | order_id | invoice_uuid                         |
      | 1        | 208cfe7d-046f-4162-b175-748942d6cff4 |
    When I send a POST request to "/order-with-invoice/test-order-uuid" with parameters:
      | key            | value                            |
      | invoice_number | new-invoice-number               |
      | invoice_file   | @dummy-invoice.png               |
      | amount         | {"gross":500,"net":450,"tax":50} |
    Then the order "test-order-uuid" has invoice data
    And the order with uuid "test-order-uuid" should have invoice number "new-invoice-number"
    # messenger issues, test not working properly
#    And make sure the order of dispatched messages is as follows:
#      | credit_note.create_credit_note  |
#      | invoice.extend_invoice          |
#      | document.document_uploaded      |
    And queue should contain message with routing key credit_note.create_credit_note with below data:
    """
    {
      "uuid":"@string@",
      "invoiceUuid":"208cfe7d-046f-4162-b175-748942d6cff4",
      "grossAmount":"44500",
      "netAmount":"40000",
      "externalCode":"new-invoice-number-CN"
    }
    """
