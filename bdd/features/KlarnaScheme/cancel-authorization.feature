Feature: Cancel authorization
  In order to cancel an authorization
  As klarna scheme
  I want to have an endpoint to cancel an existing authorization

  Background:
    Given I have orders with the following data
      | uuid              | external_id | state      | gross | net | tax | duration | workflow_name |
      | authorized-order  | KLRN1       | authorized | 1000  | 900 | 100 | 30       | order_v2      |
      | created-order     | KLRN2       | created    | 1000  | 900 | 100 | 30       | order_v2      |

  Scenario: No order found
    When I request "POST /authorizations/unknown-order/cancel"
    Then the response is 200 with body:
      """
      {
        "error_messages": [ "Authorization not found" ]
      }
      """

  Scenario: Cancel non-confirmed order
    When I request "POST /authorizations/authorized-order/cancel"
    Then the response is 200 with empty body
    And the order "KLRN1" is in state authorized

  Scenario: Cancel confirmed order
    When Debtor release limit call succeeded
    And I request "POST /authorizations/created-order/cancel"
    Then the response is 200 with empty body
    And the order "KLRN2" is in state canceled

