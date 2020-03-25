Feature:
  In order to cancel an order
  I want to have an end point to cancel my orders
  And expect empty response

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "X-Api-Key" header equal to test
    And The following notification settings exist for merchant 1:
      | notification_type | enabled |
      | order_canceled    | 1       |

  Scenario: Successful new order cancellation
    Given I have a new order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
    And I get from companies service get debtor response
    And Debtor release limit call succeeded
    When I send a POST request to "/order/CO123/cancel"
    Then the response status code should be 204
    And the response should be empty
    And the order "CO123" is in state canceled
    And Order notification should exist for order "CO123" with type "order_canceled"

  Scenario: Successful created order cancellation
    Given I have a created order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
    And I get from companies service get debtor response
    And I get from payments service get order details not found response
    And I get from payments service create ticket response
    And I get from companies service identify match response
    And I get from payments service get debtor response
    When I send a POST request to "/order/CO123/ship" with body:
    """
    {
        "invoice_number": "CO123",
        "invoice_url": "http://example.com/invoice/is/here",
        "shipping_document_url": "http://example.com/proove/is/here"
    }
    """
    And Debtor release limit call succeeded
    And I add "X-Api-Key" header equal to test
    And I add "X-Test" header equal to 1
    And I get from payments service create ticket response
    When I send a POST request to "/order/CO123/cancel"
    Then the response status code should be 204
    And the response should be empty
    And the order "CO123" is in state canceled
    And Order notification should exist for order "CO123" with type "order_canceled"
    Then the order "CO123" has invoice data

  Scenario: Successful waiting order cancellation
    Given I have a waiting order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
    And I get from companies service get debtor response
    When I send a POST request to "/order/CO123/cancel"
    Then the response status code should be 204
    And the response should be empty
    And the order "CO123" is in state canceled
    And Order notification should exist for order "CO123" with type "order_canceled"

  Scenario: Successful created order cancellation
    Given I have a created order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
    And I get from companies service get debtor response
    And Debtor release limit call succeeded
    When I send a POST request to "/order/CO123/cancel"
    Then the response status code should be 204
    And the response should be empty
    And the order "CO123" is in state canceled
    And Order notification should exist for order "CO123" with type "order_canceled"

  Scenario: Successful shipped order cancellation
    Given I have a shipped order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
    And I get from payments service get order details response
    And I get from payments service modify ticket response
    When I send a POST request to "/order/CO123/cancel"
    Then the response status code should be 204
    And the response should be empty
    And the order "CO123" is in state canceled
    And Order notification should exist for order "CO123" with type "order_canceled"

  Scenario: Unsuccessful declined order cancellation
    Given I have a declined order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
    When I send a POST request to "/order/CO123/cancel"
    Then the response status code should be 403
    And the JSON response should be:
    """
    {"errors":[{"title":"Order #CO123 can not be cancelled","code":"forbidden"}]}
    """

  Scenario: Unsuccessful canceled order cancellation
    Given I have a canceled order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
    When I send a POST request to "/order/CO123/cancel"
    Then the response status code should be 403
    And the JSON response should be:
    """
    {"errors":[{"title":"Order #CO123 can not be cancelled","code":"forbidden"}]}
    """

  Scenario: Not existing order cancellation
    When I send a POST request to "/order/CO123/cancel"
    Then the response status code should be 404
    And the JSON response should be:
    """
    {"errors":[{"title":"Order not found","code":"resource_not_found"}]}
    """

  Scenario: Fraud order cancellation
    Given I have a new order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
    And The order "CO123" was already marked as fraud
    When I send a POST request to "/order/CO123/cancel"
    Then the response status code should be 403
    And the JSON response should be:
    """
    {"errors":[{"title":"Order was marked as fraud","code":"forbidden"}]}
    """
