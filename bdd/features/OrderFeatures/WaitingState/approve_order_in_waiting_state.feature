Feature: Endpoint to approve an order in waiting state

	Background:
		Given I add "Content-type" header equal to "application/json"
		And I add "X-Test" header equal to 1
		And The following risk check definitions exist:
			| name                      |
			| available_financing_limit |
			| amount                    |
			| debtor_country            |
			| debtor_industry_sector    |
			| debtor_identified         |
			| debtor_identified_strict  |
			| delivery_address          |
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
			| delivery_address          | 1       | 0                  |
			| debtor_identified_strict  | 1       | 1                  |
			| limit                     | 1       | 0                  |
			| debtor_not_customer       | 1       | 1                  |
			| debtor_blacklisted        | 1       | 1                  |
			| debtor_overdue            | 1       | 1                  |
			| company_b2b_score         | 1       | 1                  |
		And The following notification settings exist for merchant 1:
			| notification_type | enabled |
			| order_approved    | 1       |

	Scenario: Order doesn't exists
		Given I have a new order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
		When I send a POST request to "/private/order/WrongOrderCode/approve"
		Then the response status code should be 404

	Scenario: Order is not in waiting state
		Given I have a new order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
		When I send a POST request to "/private/order/test-order-uuidCO123/approve"
		Then the response status code should be 403
		And the JSON response should be:
    """
    {"errors":[{"title":"Cannot approve the order. Order is not in waiting state.","code":"forbidden"}]}
    """

	Scenario: Fails when order is in pre_waiting state
		Given I have a pre_waiting order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
		When I send a POST request to "/private/order/test-order-uuidCO123/approve"
		Then the response status code should be 403
		And the JSON response should be:
    """
    {"errors":[{"title":"Cannot approve the order. Order is not in waiting state.","code":"forbidden"}]}
    """

	Scenario: Failed to approve order in waiting state because if failed checks
		Given I have a waiting order "CO123" with amounts 1001/901/100, duration 30 and comment "test order"
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
		And I get from companies service get debtor response
    And Debtor has insufficient limit
		When I send a POST request to "/private/order/test-order-uuidCO123/approve"
		Then the response status code should be 403
		And the JSON response should be:
    """
        {"errors":[{"title":"Cannot approve the order. Limit check failed","code":"forbidden"}]}
    """
		And the order CO123 is in state waiting
		And the order CO123 has risk check limit failed

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
		And I get from companies service get debtor response
		And Debtor has sufficient limit
		And Debtor lock limit call succeeded
		When I send a POST request to "/private/order/test-order-uuidCO123/approve"
		Then the response status code should be 204
		And the order CO123 is in state created

	Scenario: Order in waiting state because of limit check - rerun limit check and fail in limit lock
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
		And I get from companies service get debtor response
		And Debtor has insufficient limit
		When I send a POST request to "/private/order/test-order-uuidCO123/approve"
		Then the response status code should be 403
		And the order CO123 is in state waiting

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
		And I get from companies service get debtor response
		And Debtor has sufficient limit
		And Debtor lock limit call succeeded
		When I send a POST request to "/private/order/test-order-uuidCO123/approve"
		Then the response status code should be 204
		And the order CO123 is in state created

	Scenario: Order in waiting state because of blacklisted check - rerun blacklisted check, but limit check failed
		Given I have a waiting order "CO123" with amounts 10000000/9000000/1000000, duration 30 and comment "test order"
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
		And Debtor has insufficient limit
		And I get from companies service get debtor response
		When I send a POST request to "/private/order/test-order-uuidCO123/approve"
		Then the JSON response should be:
    """
    {"errors":[{"title":"Cannot approve the order. Limit check failed","code":"forbidden"}]}
    """
		And the response status code should be 403
		And the order CO123 is in state waiting
		And the order CO123 has risk check limit failed

	Scenario: Order in waiting state because of delivery_address check - approve order and override delivery_address check
		Given I have a waiting order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
		And The following risk check results exist for order CO123:
			| check_name                | is_passed |
			| available_financing_limit | 1         |
			| amount                    | 1         |
			| debtor_country            | 1         |
			| debtor_industry_sector    | 1         |
			| debtor_identified         | 1         |
			| debtor_blacklisted        | 1         |
			| delivery_address          | 0         |
			| limit                     | 1         |
			| debtor_not_customer       | 1         |
			| debtor_blacklisted        | 1         |
			| debtor_overdue            | 1         |
			| company_b2b_score         | 1         |
		And I get from companies service get debtor response
		And Debtor has sufficient limit
		And Debtor lock limit call succeeded
		When I send a POST request to "/private/order/test-order-uuidCO123/approve"
		Then the response status code should be 204
		And the order CO123 is in state created
		And Order notification should exist for order "CO123" with type "order_approved"
