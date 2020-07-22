Feature: APIS-1077
  In order to update an order
  I want to have an end point to update my orders
  And expect empty response

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "X-Api-Key" header equal to test
    And I get from payments service modify ticket response
    And I get from companies service get debtor response
    And I get from companies service "/debtor/1/unlock" endpoint response with status 200 and body
    """
    """

  Scenario: Authorised oauth user without a Merchant Api-Key authentication cannot update orders
    Given a merchant user exists with permission CONFIRM_ORDER_PAYMENT
    And I get from Oauth service a valid user token
    And I add "X-Api-Key" header equal to invalid_key
    And I add "Authorization" header equal to "Bearer someToken"
    And I have a "created" order with amounts 1000/900/100, duration 30 and comment "test order"
    When I send a PATCH request to "/order/test-order-uuid" with body:
    """
    {
      "order_id": "foobar",
      "duration": 60,
      "amount": {
        "gross": 500,
        "net": 400,
        "tax": 100
      }
    }
    """
    Then the response status code should be 401
    And the JSON response should be:
    """
      {"errors":[{"title":"Unauthorized","code":"unauthorized"}]}
    """
    And the order with uuid "test-order-uuid" should have amounts 1000/900/100

  Scenario Template: Success 1: Partial provided data is OK and update is successful on any non-final state
    Given I have a "<state>" order with amounts 1000/900/100, duration 30 and comment "test order"
    And Salesforce DCI API responded for the order UUID "test-order-uuid" with no collections taking place
    And Debtor release limit call succeeded
    When I send a PATCH request to "/order/test-order-uuid" with body:
    """
    {
      "order_id": "foobar",
      "duration": 60,
      "amount": {
        "gross": 500,
        "net": 400,
        "tax": 100
      }
    }
    """
    Then the response status code should be 204
    And the response should be empty
    Examples:
      | state    |
      | created  |
      | shipped  |
      | paid_out |
      | late     |
      | waiting  |
    And the order with uuid "test-order-uuid" should have amounts 500/400/100


  Scenario Template: Success 2: Full provided data is OK and update is successful only when state is or was shipped
    Given I have a "<state>" order with amounts 1000/900/100, duration 30 and comment "test order"
    And Salesforce DCI API responded for the order UUID "test-order-uuid" with no collections taking place
    When I send a PATCH request to "/order/test-order-uuid" with body:
    """
    {
      "order_id": "foobar",
      "invoice_number": "foobar_123",
      "invoice_url": "/foobar_123-1.pdf",
      "duration": 60,
      "amount": {
        "gross": 500,
        "net": 400,
        "tax": 100
      }
    }
    """
    Then the response status code should be 204
    And the response should be empty
    Examples:
      | state    |
      | shipped  |
      | paid_out |
      | late     |
    And the order with uuid "test-order-uuid" should have amounts 500/400/100

  Scenario: Order does not exist
    When I send a PATCH request to "/order/abc123" with body:
    """
    {
      "duration": 50,
      "amount": {
        "net": 500,
        "gross": 500,
        "tax": 0
      }
    }
    """
    Then the response status code should be 404

  Scenario: Order is marked as fraud
    Given I have a created order "abc123" with amounts 1000/900/100, duration 30 and comment "test order"
    And The order "abc123" was already marked as fraud
    When I send a PATCH request to "/order/abc123" with body:
    """
    {
      "duration": 30,
      "amount": {
        "gross": 500,
        "net": 400,
        "tax": 100
      }
    }
    """
    Then the response status code should be 403
    And the JSON response should be:
    """
    {"errors":[{"title":"Order was marked as fraud","code":"forbidden"}]}
    """

  Scenario: Update late order when it's passed to DCI collections
    Given I have a late order "abc123" with amounts 1000/900/100, duration 30 and comment "test order"
    And Salesforce DCI API responded for the order UUID "test-order-uuidabc123" with the ongoing collections
    When I send a PATCH request to "/order/abc123" with body:
    """
    {
      "duration": 30,
      "amount": {
        "gross": 500,
        "net": 400,
        "tax": 100
      }
    }
    """
    Then the response status code should be 403
    And the JSON response should be:
    """
    {"errors":[{"title":"Order amount cannot be updated","code":"forbidden"}]}
    """

  Scenario: Order external id is blank
    Given I have a created order with amounts 1000/900/100, duration 30 and comment "test order"
    When I send a PATCH request to "/order/test-order-uuid" with body:
    """
    {
      "order_id": ""
    }
    """
    Then the response status code should be 400
    And the JSON response should be:
    """
    {
     "errors":[
      {
        "title": "This value should be null or non-blank string.",
        "code": "request_validation_error",
        "source": "external_code"
      }
     ]
    }
    """

  Scenario Template: Provided amount is wrong: tax is wrongly calculated
    Given I have a new order "abc123" with amounts 1000/900/100, duration 30 and comment "test order"
    When I send a PATCH request to "/order/abc123" with body:
    """
    {
      "amount": {
        "gross": <gross>,
        "net": <net>,
        "tax": <tax>
      }
    }
    """
    Examples:
      | gross | net | tax |
      | 500   | 150 | 100 |
    Then the response status code should be 400
    And the order with uuid "test-order-uuidabc123" should have amounts 1000/900/100
    And the response should contain "gross is not equal to net + tax"

  Scenario Template: Provided amount is wrong: amount is zero or negative
    Given I have a new order "abc123" with amounts 1000/900/100, duration 30 and comment "test order"
    When I send a PATCH request to "/order/abc123" with body:
    """
    {
      "amount": {
        "gross": <gross>,
        "net": <net>,
        "tax": <tax>
      }
    }
    """
    Examples:
      | gross | net | tax |
      | 0     | 0   | 0   |
      | -1    | -1  | 0   |
    Then the response status code should be 400
    And the response should contain "This value should be greater than 0"
    And the order with uuid "test-order-uuidabc123" should have amounts 1000/900/100

  Scenario Template: Changing amount is not allowed because of order state
    Given I have a "<state>" order "abc123" with amounts 1000/900/100, duration 30 and comment "test order"
    When I send a PATCH request to "/order/abc123" with body:
    """
    {
      "amount": {
        "gross": 500,
        "net": 400,
        "tax": 100
      }
    }
    """
    Then the response status code should be 403
    And the JSON response should be:
    """
    {"errors":[{"title":"Order amount cannot be updated","code":"forbidden"}]}
    """
    Examples:
      | state      |
      | new        |
      | authorized |
      | declined   |
      | complete   |
      | canceled   |
    And the order with uuid "test-order-uuidabc123" should have amounts 1000/900/100

  Scenario: Changing amount is not allowed because it is higher than previous
    Given I have a created order "abc123" with amounts 1000/900/100, duration 30 and comment "test order"
    When I send a PATCH request to "/order/abc123" with body:
    """
    {
      "amount": {
        "gross": 2000,
        "net": 1900,
        "tax": 100
      }
    }
    """
    Then the response status code should be 403
    And the JSON response should be:
    """
    {"errors":[{"title":"Order amount cannot be updated","code":"forbidden"}]}
    """
    And the order with uuid "test-order-uuidabc123" should have amounts 1000/900/100

  Scenario Template: Changing duration is not allowed because of order state
    Given I have a "<state>" order "abc123" with amounts 1000/900/100, duration 30 and comment "test order"
    When I send a PATCH request to "/order/abc123" with body:
    """
    {
      "duration": 31
    }
    """
    Then the response status code should be 403
    And the JSON response should be:
    """
    {"errors":[{"title":"Order duration cannot be updated","code":"forbidden"}]}
    """
    Examples:
      | state      |
      | new        |
      | authorized |
      | declined   |
      | complete   |
      | canceled   |

  Scenario: Changing duration is not allowed because it is lower than previous
    Given I have a created order "abc123" with amounts 1000/900/100, duration 30 and comment "test order"
    When I send a PATCH request to "/order/abc123" with body:
    """
    {
      "duration": 20
    }
    """
    Then the response status code should be 403
    And the JSON response should be:
    """
    {"errors":[{"title":"Order duration cannot be updated","code":"forbidden"}]}
    """

  Scenario: Changing duration is not allowed because it is higher than 120
    Given I have a created order "abc123" with amounts 1000/900/100, duration 30 and comment "test order"
    When I send a PATCH request to "/order/abc123" with body:
    """
    {
      "duration": 121
    }
    """
    Then the response status code should be 400
    And the JSON response should be:
    """
    {"errors":[{"title":"This value should be between 1 and 120.","code":"request_validation_error","source":"duration"}]}
    """

  Scenario: Changing external code is not allowed because it was already set
    Given I have a created order "abc123" with amounts 1000/900/100, duration 30 and comment "test order"
    When I send a PATCH request to "/order/abc123" with body:
    """
    {
      "order_id": "abcdef456"
    }
    """
    Then the response status code should be 403
    And the JSON response should be:
    """
    {"errors":[{"title":"Order external code cannot be updated","code":"forbidden"}]}
    """

  Scenario Template: Changing invoice number is not allowed because of order state
    Given I have a "<state>" order "abc123" with amounts 1000/900/100, duration 30 and comment "test order"
    When I send a PATCH request to "/order/abc123" with body:
    """
    {
      "invoice_number": "foobar_123"
    }
    """
    Then the response status code should be 403
    And the JSON response should be:
    """
    {"errors":[{"title":"Order invoice number cannot be updated","code":"forbidden"}]}
    """
    Examples:
      | state        |
      | new          |
      | authorized   |
      | declined     |
      | complete     |
      | canceled     |
      | pre_approved |
      | pre_waiting  |
      | waiting      |
      | created      |

  Scenario Template: Changing invoice URL is not allowed because of order state
    Given I have a "<state>" order "abc123" with amounts 1000/900/100, duration 30 and comment "test order"
    When I send a PATCH request to "/order/abc123" with body:
    """
    {
      "invoice_url": "/foobar_123-1.pdf"
    }
    """
    Then the response status code should be 403
    And the JSON response should be:
    """
    {"errors":[{"title":"Order invoice URL cannot be updated","code":"forbidden"}]}
    """
    Examples:
      | state        |
      | new          |
      | authorized   |
      | declined     |
      | complete     |
      | canceled     |
      | pre_approved |
      | pre_waiting  |
      | waiting      |
      | created      |
