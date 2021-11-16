Feature: Extend authorization expiration

  Background:
    Given I have orders with the following data
      | uuid               | external_id | Expires At          | state   |
      | confirmed-order-id | C3PO        | 2021-10-02 12:00:00 | created |
    And I add header "Authorization" with "Basic a2xhcm5hJTQwYmlsbGllLmlvOmtsYXJuYTEyMzQ="
    And GraphQL will respond to getMerchantDebtorDetails query

  Scenario: Missing expires_at
    When I request "POST /authorizations/confirmed-order-id/extend-expiration" with body:
      """
      {}
      """
    Then the response is 200 with body:
      """
      {
        "error_messages": [ "Invalid request" ]
      }
      """

  Scenario: Authorization not found
    When I request "POST /authorizations/unknown-order-id/extend-expiration" with body:
      """
      {
        "expires_at": "2021-10-12 10:00:00"
      }
      """
    Then the response is 200 with body:
      """
      {
        "error_messages": [ "Authorization not found" ]
      }
      """

  Scenario: New expiration is before current one
    When I request "POST /authorizations/confirmed-order-id/extend-expiration" with body:
      """
      {
        "expires_at": "2021-10-01 10:00:00"
      }
      """
    Then the response is 200 with body:
      """
      {
        "error_messages": [ "Expiration could not be extended" ]
      }
      """

  Scenario: Extend expiration
    When I request "POST /authorizations/confirmed-order-id/extend-expiration" with body:
      """
      {
        "expires_at": "2021-10-12 10:00:00"
      }
      """
    Then the response is 200 with body:
      """
      {
        "result": "accepted"
      }
      """
    And there should be the following registered orders:
      | Id                 | External Id | Expires At          | State   |
      | confirmed-order-id | C3PO        | 2021-10-12 10:00:00 | created |
