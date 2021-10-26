Feature: Update authorization customer details

  Scenario: Feature not supported
    When I request "POST /authorizations/any-order-id/customer-details"
    Then the response is 200 with body:
      """
      {
        "error_messages": [ "Not supported" ]
      }
      """
