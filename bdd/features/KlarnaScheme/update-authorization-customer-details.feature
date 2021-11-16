Feature: Update authorization customer details

  Background:
    Given I add header "Authorization" with "Basic a2xhcm5hJTQwYmlsbGllLmlvOmtsYXJuYTEyMzQ="
    And GraphQL will respond to getMerchantDebtorDetails query

  Scenario: Feature not supported
    When I request "POST /authorizations/any-order-id/customer-details"
    Then the response is 200 with body:
      """
      {
        "error_messages": [ "Not supported" ]
      }
      """
