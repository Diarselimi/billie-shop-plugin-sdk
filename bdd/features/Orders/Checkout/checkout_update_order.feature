Feature: As a merchant debtor, I want to use my initially provided address (which was not identified as company)
  as a billing address.

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And The following risk check definitions exist:
      | name                      |
      | available_financing_limit |
      | amount                    |
      | line_items                |
      | debtor_country            |
      | debtor_industry_sector    |
      | debtor_identified         |
      | limit                     |
      | debtor_not_customer       |
      | company_b2b_score         |
      | debtor_identified_strict  |
    And The following merchant risk check settings exist for merchant 1:
      | risk_check_name           | enabled | decline_on_failure |
      | available_financing_limit | 1       | 0                  |
      | amount                    | 1       | 0                  |
      | line_items                | 1       | 1                  |
      | debtor_country            | 1       | 1                  |
      | debtor_industry_sector    | 1       | 1                  |
      | debtor_identified         | 1       | 1                  |
      | limit                     | 1       | 1                  |
      | debtor_not_customer       | 1       | 1                  |
      | company_b2b_score         | 1       | 1                  |
      | debtor_identified_strict  | 1       | 1                  |
    And GraphQL will respond to getMerchantDebtorDetails query
    And I get from companies service "/companies/c7be46c0-e049-4312-b274-258ec5aeeb70/billing-address" endpoint response with status 200 and body
      """
      {
        "billing_address": {
            "uuid": "4b2228d3-1fa7-401c-8a92-5e8c4827caa3",
            "city": "Berlin",
            "country": "DE",
            "house_number": "7",
            "postal_code": "10969",
            "street": "Charlottenstr.",
            "addition": null
        }
      }
      """

  Scenario Template: The billing address is properly updated
    Examples:
      | state       |
      | pre_waiting |
      | authorized  |
    Given I have a <state> order "CO123" with amounts 100.0/90.0/10.0, duration 30 and checkout session "123123"
    When I send a POST request to "/checkout-session/123123/update" with body:
    """
      {
        "billing_address": {
            "city": "Berlin",
            "country": "DE",
            "house_number": "7",
            "postal_code": "10969",
            "street": "Charlottenstr.",
            "addition": null
        }
      }
    """
    Then the response status code should be 204
    And the order with code "CO123" should have company_billing_address set to "4b2228d3-1fa7-401c-8a92-5e8c4827caa3"
    And the debtor external data for order with code "CO123" should have a billing address

  Scenario Template: The duration and duration extension are properly updated
    Examples:
      | state       |
      | pre_waiting |
      | authorized  |
    Given I have a <state> order "CO123" with amounts 100.0/90.0/10.0, duration 30 and checkout session "123123"
    When I send a POST request to "/checkout-session/123123/update" with body:
    """
      {
        "duration": 45
      }
    """
    Then the response status code should be 204
    And the order with code "CO123" at "duration" equals 45
    And the order with code "CO123" at "durationExtension" equals 15

  Scenario: The billing address is properly updated, even with an already used checkout session UUID
    Given I have a "authorized" order "CO123" with amounts 100.0/90.0/10.0, duration 30 and checkout session "123123-inactive"
    When I send a POST request to "/checkout-session/123123-inactive/update" with body:
    """
      {
        "billing_address": {
            "city": "Berlin",
            "country": "DE",
            "house_number": "7",
            "postal_code": "10969",
            "street": "Charlottenstr.",
            "addition": null
        }
      }
    """
    Then the response status code should be 204
    And the order with code "CO123" should have company_billing_address set to "4b2228d3-1fa7-401c-8a92-5e8c4827caa3"
    And the debtor external data for order with code "CO123" should have a billing address

  Scenario: The checkout update call fails when the session does not exist
    When I send a POST request to "/checkout-session/456789/update" with body:
    """
      {
        "billing_address": {
            "city": "Berlin",
            "country": "DE",
            "house_number": "7",
            "postal_code": "10969",
            "street": "Charlottenstr.",
            "addition": null
        }
      }
    """
    Then the response status code should be 401


  Scenario Template: The checkout update call fails when the order is not in authorized state
    Examples:
      | state    |
      | new      |
      | created  |
      | declined |
      | shipped  |
      | paid_out |
      | late     |
      | waiting  |
      | complete |
      | canceled |
    Given I have a <state> order "CO123" with amounts 100.0/90.0/10.0, duration 30 and checkout session "123123"
    When I send a POST request to "/checkout-session/123123/update" with body:
    """
      {
        "billing_address": {
            "city": "Berlin",
            "country": "DE",
            "house_number": "7",
            "postal_code": "10969",
            "street": "Charlottenstr.",
            "addition": null
        }
      }
    """
    Then the response status code should be 404
