Feature: API should be able to be accessed as different versions

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "X-Api-Key" header equal to test
    And I have a new order "XF43Y2" with amounts 1000/900/100, duration 30 and comment "test order"
    And I get from invoice-butler service good response
    And the following invoice data exists:
      | order_id | invoice_uuid                         |
      | 1        | 208cfe7d-046f-4162-b175-748942d6cff4 |
    And GraphQL will respond to getMerchantDebtorDetails query

  Scenario: Can access non-prefixed public endpoints
    When I send a GET request to "/order/XF43Y2"
    Then the response status code should be 200

  Scenario: Can access public-prefixed endpoints
    When I send a GET request to "/public/order/XF43Y2"
    Then the response status code should be 200

  Scenario: Can access public/api/v1 prefixed endpoints
    When I send a GET request to "/public/api/v1/order/XF43Y2"
    Then the response status code should be 200

  Scenario: Can access public/api/v2 prefixed endpoints
    When I send a GET request to "/public/api/v2/order/XF43Y2"
    Then the response status code should be 200

  Scenario: Cannot access private API from public/* route
    When I send a POST request to "/public/order/XF43Y2/approve"
    Then the response status code should be 404
    And the response should contain "No route found"

  Scenario: Cannot access private API from public/api/v1/* route
    When I send a POST request to "/public/api/v1/order/XF43Y2/approve"
    Then the response status code should be 404
    And the response should contain "No route found"

  Scenario: Cannot access private API from public/api/v2/* route
    When I send a POST request to "/public/api/v2/order/XF43Y2/approve"
    Then the response status code should be 404
    And the response should contain "No route found"

  Scenario: Can access private API from private/* route
    Given The order "XF43Y2" has UUID "test-uuid"
    When I send a POST request to "/private/order/test-uuid/approve"
    Then the response status code should be 403
    And the response should contain "Cannot approve the order. Order is not in waiting state."
