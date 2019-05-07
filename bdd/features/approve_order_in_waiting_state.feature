Feature: Endpoint to approve an order in waiting state

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "X-Api-Key" header equal to test
    And The following risk check definitions exist:
      | name                      |
      | available_financing_limit |
      | amount                    |
      | debtor_country            |
      | debtor_industry_sector    |
      | debtor_identified         |
      | debtor_identified_strict  |
      | limit                     |
      | debtor_not_customer       |
      | debtor_blacklisted        |
      | debtor_overdue            |
      | company_b2b_score         |
    And The following merchant risk check settings exist for merchant 1:
      | risk_check_name           | enabled | decline_on_failure |
      | available_financing_limit | 1       | 1                  |
      | amount                    | 1       | 1                  |
      | debtor_country            | 1       | 1                  |
      | debtor_industry_sector    | 1       | 1                  |
      | debtor_identified         | 1       | 1                  |
      | debtor_identified_strict  | 1       | 1                  |
      | limit                     | 1       | 0                  |
      | debtor_not_customer       | 1       | 1                  |
      | debtor_blacklisted        | 1       | 1                  |
      | debtor_overdue            | 1       | 1                  |
      | company_b2b_score         | 1       | 1                  |

  Scenario: Order doesn't exists
    Given I have a new order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
    When I send a POST request to "/order/WrongOrderCode/approve"
    Then the response status code should be 404

  Scenario: Order is not in waiting state
    Given I have a new order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
    When I send a POST request to "/order/CO123/approve"
    Then the response status code should be 403
    And the JSON response should be:
	"""
	{"error": "Cannot approve the order. Order is not in waiting state."}
	"""

  Scenario: Failed to approve order in waiting state because if failed checks
    Given I have a waiting order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
    And The following risk check results exist for order CO123:
      | check_name                | is_passed |
      | available_financing_limit | 1         |
      | amount                    | 1         |
      | debtor_country            | 1         |
      | debtor_industry_sector    | 1         |
      | debtor_identified         | 1         |
      | debtor_identified_strict  | 1         |
      | limit                     | 0         |
      | debtor_not_customer       | 1         |
      | debtor_blacklisted        | 1         |
      | debtor_overdue            | 1         |
      | company_b2b_score         | 1         |
    And I get from companies service identify match and good decision response
    And I get from companies service "/debtor/1/lock" endpoint response with status 412 and body
      """
      {}
      """
    When I send a POST request to "/order/CO123/approve"
    Then the response status code should be 403
    And the JSON response should be:
	"""
	{"error": "Cannot approve the order. failed risk checks: debtor_limit_exceeded"}
	"""
    And the order CO123 is in state waiting

  Scenario: Order in waiting state because of limit check - rerun limit check and successfully approve the order
    Given I have a waiting order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
    And The following risk check results exist for order CO123:
      | check_name                | is_passed |
      | available_financing_limit | 1         |
      | amount                    | 1         |
      | debtor_country            | 1         |
      | debtor_industry_sector    | 1         |
      | debtor_identified         | 1         |
      | debtor_identified_strict  | 1         |
      | limit                     | 0         |
      | debtor_not_customer       | 1         |
      | debtor_blacklisted        | 1         |
      | debtor_overdue            | 1         |
      | company_b2b_score         | 1         |
    And I get from companies service identify match and good decision response
    When I send a POST request to "/order/CO123/approve"
    Then the response status code should be 200
    And the order CO123 is in state created

  Scenario: Order in waiting state because of blacklisted check - rerun blacklisted check and successfully approve the order
    Given I have a waiting order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
    And The following risk check results exist for order CO123:
      | check_name                | is_passed |
      | available_financing_limit | 1         |
      | amount                    | 1         |
      | debtor_country            | 1         |
      | debtor_industry_sector    | 1         |
      | debtor_identified         | 1         |
      | debtor_identified_strict  | 1         |
      | limit                     | 1         |
      | debtor_not_customer       | 1         |
      | debtor_blacklisted        | 0         |
      | debtor_overdue            | 1         |
      | company_b2b_score         | 1         |
    And I get from companies service identify match and good decision response
    When I send a POST request to "/order/CO123/approve"
    Then the response status code should be 200
    And the order CO123 is in state created
