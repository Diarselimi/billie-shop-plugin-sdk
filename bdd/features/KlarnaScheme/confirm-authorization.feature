Feature: Confirm authorization

  Background:
    Given I have orders with the following data
      | uuid              | external_id | state    | gross | net | tax | duration | payment_uuid |
      | unconfirmed-order | C3PO        | new      | 1000  | 900 | 100 | 30       | payment-id-1 |
      | confirmed-order   | R2D2        | declined | 1000  | 900 | 100 | 30       | payment-id-2 |
    And GraphQL will respond to getMerchantDebtorDetails query

  Scenario: No order found
    When I request "POST /authorizations/unknown-order/confirm"
    Then the response is 200 with body:
      """
      {
        "error_messages": [ "Authorization not found" ]
      }
      """

  Scenario: Order already confirmed
    When I request "POST /authorizations/confirmed-order/confirm"
    Then the response is 200 with empty body
    And the order "R2D2" is in state declined

  Scenario: Order is confirmed
    When I request "POST /authorizations/unconfirmed-order/confirm"
    Then the response is 200 with empty body
    And the order "C3PO" is in state created
