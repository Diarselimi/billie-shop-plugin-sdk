Feature: Post confirm adjust

  Background:
    Given I have orders with the following data
      | uuid                 | external_id | state   | gross | net | tax | duration | payment_uuid                         |
      | confirmed-order-uuid | C3PO        | created | 1000  | 900 | 100 | 30       | 6d6b4222-be8c-11e9-9cb5-2a2ae2dbcce4 |
    And I add header "Authorization" with "Basic a2xhcm5hJTQwYmlsbGllLmlvOmtsYXJuYTEyMzQ="
    And GraphQL will respond to getMerchantDebtorDetails query

  Scenario: Missing amount
    When I request "POST /authorizations/confirmed-order-uuid/adjust-order" with body:
      """
      {
        "tax_amount": 10
      }
      """
    Then the response is 200 with body:
      """
      {
        "error_messages": [ "Invalid request" ]
      }
      """

  Scenario: Missing tax amount
    When I request "POST /authorizations/confirmed-order-uuid/adjust-order" with body:
      """
      {
        "amount": 1000
      }
      """
    Then the response is 200 with body:
      """
      {
        "error_messages": [ "Invalid request" ]
      }
      """

  Scenario: Authorization not found
    When I request "POST /authorizations/unknown-order-uuid/adjust-order" with body:
      """
      {
        "amount": 1000,
        "tax_amount": 10
      }
      """
    Then the response is 200 with body:
      """
      {
        "error_messages": [ "Authorization not found" ]
      }
      """

  Scenario: Higher new amount
    When I request "POST /authorizations/confirmed-order-uuid/adjust-order" with body:
      """
      {
        "amount": 1000000,
        "tax_amount": 10000
      }
      """
    Then the response is 200 with body:
      """
      {
        "error_messages": [ "Authorization could not be adjusted" ]
      }
      """

  Scenario: Lower new amount
    When Debtor release limit call succeeded
    And I request "POST /authorizations/confirmed-order-uuid/adjust-order" with body:
      """
      {
        "amount": 1000,
        "tax_amount": 10
      }
      """
    Then the response is 200 with body:
      """
      {
        "result": "accepted"
      }
      """
    And the order with uuid "confirmed-order-uuid" should have amounts 10/9.90/0.10
