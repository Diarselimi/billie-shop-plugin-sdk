Feature: We should be able to update an order if it's not in states declined/canceled/complete.
  Also we have to check with salesforce if the payments are being collected.
  We need to send a credit note message to Invoice-butler.
  And expect empty response

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "Authorization" header equal to "Bearer someToken"
    And I get from Oauth service a valid user token
    And a merchant user exists with permission UPDATE_ORDERS
    And I add "X-Api-Key" header equal to test
    And GraphQL will respond to getMerchantDebtorDetails query

  Scenario: I successfully update an order in state created.
    Given I have a created order with amounts 1000/900/100, duration 30 and comment "test order"
    And Salesforce DCI API responded for the order UUID "test-order-uuid" with no collections taking place
    And Debtor release limit call succeeded
    And I get from invoice-butler service good response
    And the following invoice data exists:
      | order_id | invoice_uuid                         |
      | 1        | 208cfe7d-046f-4162-b175-748942d6cff4 |
    When I send a POST request to "/orders/test-order-uuid" with body:
    """
    {
      "external_code": "foobar",
      "amount": {
        "gross": 123.33,
        "net": 100.33,
        "tax": 23
      }
    }
    """
    Then the response status code should be 204
    And the response should be empty
