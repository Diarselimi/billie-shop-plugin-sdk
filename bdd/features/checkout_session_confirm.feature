Feature: As a merchant, i should be able to create an order if I provide a valid session_id

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "X-Api-Key" header equal to test
    And The following risk check definitions exist:
      | name                              |
      | available_financing_limit         |
      | amount                            |
      | debtor_country                    |
      | debtor_industry_sector            |
      | debtor_identified                 |
      | limit                             |
      | debtor_not_customer               |
      | debtor_blacklisted                |
      | debtor_overdue                    |
      | company_b2b_score                 |
      | debtor_identified_strict          |
    And The following merchant risk check settings exist for merchant 1:
      | risk_check_name                   |	enabled	|	decline_on_failure	|
      | available_financing_limit         |	1		|	1					|
      | amount                            |	1		| 	1					|
      | debtor_country                    |	1		| 	1					|
      | debtor_industry_sector            |	1		| 	1					|
      | debtor_identified                 |	1		| 	1					|
      | limit                             |	1		| 	1					|
      | debtor_not_customer               |	1		| 	1					|
      | debtor_blacklisted                |	1		| 	1					|
      | debtor_overdue                    |	1		| 	1					|
      | company_b2b_score                 |	1		| 	1					|
      | debtor_identified_strict          |	1		| 	1					|
    And I get from companies service get debtor response
    And I get from payments service get debtor response

  Scenario: I successfully confirm the order by sending the same amounts and duration.
    Given I have a authorized order "CO123" with amounts 100.0/90.0/10.0, duration 30 and comment "test order"
    And I get from companies service identify match response
    And I get from companies service get debtor response
    And I send a PUT request to "/checkout-session/123123/confirm" with body:
    """
    {
       "amount":{
          "net":90.0,
          "gross":100.0,
          "tax":10.0
       },
       "duration":30
    }
    """
    Then the response status code should be 202
    And the order CO123 is in state created

  Scenario:
    I fail to confirm the order if I send the wrong confirm request
    Given I have a authorized order "CO123" with amounts 42.30/55.2/2, duration 99 and comment "test order"
    And I get from companies service identify match response
    And I get from companies service get debtor response
    And I send a PUT request to "/checkout-session/123123/confirm" with body:
    """
    {
       "amount":{
          "net":55.2,
          "gross":43.30,
          "tax":10.10
       },
       "duration":30
    }
    """
    Then the order CO123 is in state authorized
    And the response status code should be 400

  Scenario:
    I fail to confirm the order if I send the wrong duration only
    Given I have a authorized order "CO123" with amounts 43.30/55.2/10.10, duration 30 and comment "test order"
    And I get from companies service identify match response
    And I get from companies service get debtor response
    And I send a PUT request to "/checkout-session/123123/confirm" with body:
    """
    {
       "amount":{
          "net":55.2,
          "gross":43.30,
          "tax":10.10
       },
       "duration":31
    }
    """
    Then the order CO123 is in state authorized
    And the response status code should be 400

  Scenario:
    I fail to confirm the order if I do not send a request body
    Given I have a authorized order "CO123" with amounts 43.30/55.2/10.10, duration 30 and comment "test order"
    And I get from companies service identify match response
    And I get from companies service get debtor response
    And I send a PUT request to "/checkout-session/123123/confirm"
    Then the JSON response should be:
    """
    {"errors":[{"source":"amount.net","title":"This value should not be blank.","code":"request_validation_error"},{"source":"amount.gross","title":"This value should not be blank.","code":"request_validation_error"},{"source":"amount.tax","title":"This value should not be blank.","code":"request_validation_error"},{"source":"duration","title":"This value should not be blank.","code":"request_validation_error"}]}
    """
    And the response status code should be 400

  Scenario:
    I fail to find the order by giving a wrong sessionUuid
    Given I send a PUT request to "/checkout-session/123123/confirm" with body:
    """
    {
       "amount":{
          "net":50.0,
          "gross":50.0,
          "tax":0.0
       },
       "duration":30
    }
    """
    And the response status code should be 404
