Feature:
  Purpose - Delete an unconfirmed Authorization.
  Preconditions - The payment_method_reference refers to an unconfirmed Authorization.
  Scheme operations - The Authorization is deleted. The delete notification should always be accepted by the Payment Method.

  Background:
    Given I have orders with the following data
      | uuid                 | external_id | state      | gross | net | tax | duration | payment_uuid                         |
      | confirmed-order-uuid | C3PO        | authorized | 1000  | 900 | 100 | 30       | 6d6b4222-be8c-11e9-9cb5-2a2ae2dbcce4 |
    And I add header "Authorization" with "Basic a2xhcm5hJTQwYmlsbGllLmlvOmtsYXJuYTEyMzQ="
    And GraphQL will respond to getMerchantDebtorDetails query

  Scenario: Authorization does not exist.
    When I request "POST /authorizations/00112233-4455-6677-8899-aabbccddeeff/delete" with body:
      """
      {
      }
      """
    Then the response is 200 with body:
      """
      {
        "error_messages": [ "Authorization not found" ]
      }
      """

  Scenario: Successfully delete the authorization.
    When I request "POST /authorizations/confirmed-order-uuid/delete"
    Then the response is 200 with empty body
    And the order "C3PO" is in state "declined"

