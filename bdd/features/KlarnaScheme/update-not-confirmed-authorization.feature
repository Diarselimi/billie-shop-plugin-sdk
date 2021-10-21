Feature: Update not confirmed authorization

  Scenario: Feature not supported
    When I request "POST /authorizations/some-order-uuid"
    Then the response is 200 with body:
      """
      {
        "error_messages": [ "Not supported" ]
      }
      """
