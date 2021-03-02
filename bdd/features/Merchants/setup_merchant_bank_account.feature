Feature: API endpoint for "POST /merchant/bank-account" (ticket APIS-1727)

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1

  Scenario: Missing authorization header
    When I send a POST request to "/merchant/bank-account"
    Then the JSON response should be:
    """
      {"errors":[{"title":"Unauthorized","code":"unauthorized"}]}
    """
    And the response status code should be 401

  Scenario: Authenticated user without MANAGE_ONBOARDING permission fails to call POST /merchant/bank-account
    Given a merchant user exists with permission FOOBAR
    And I get from Oauth service a valid user token
    And I add "Authorization" header equal to "Bearer someToken"
    When I send a POST request to "/merchant/bank-account"
    Then the JSON response should be:
    """
      {"errors":[{"title":"Access Denied.","code":"forbidden"}]}
    """
    And the response status code should be 403

  Scenario: Failed authorised call to POST /merchant/bank-account if BIC service fails
    Given a merchant user exists with permission MANAGE_ONBOARDING
    And I get from Oauth service a valid user token
    And I get from Oauth service revoke token endpoint a successful response
    And I add "Authorization" header equal to "Bearer SomeTokenHere"
    And I get from BIC lookup service call to "/bankcodes/50010517.json" a response with status 200 and body:
    """
      {"msg": "bank code not found"}
    """
    When I send a POST request to "/merchant/bank-account" with body:
    """
      {"iban":"DE42500105171514412424", "tc_accepted":true}
    """
    Then the response status code should be 400
    And the JSON response should be:
    """
      {"errors":[{"title":"BIC code cannot be found out of the given IBAN.","code":"request_invalid"}]}
    """

  Scenario: Failed authorised call to POST /merchant/bank-account when data is invalid
    Given a merchant user exists with permission MANAGE_ONBOARDING
    And I get from Oauth service a valid user token
    And I get from Oauth service revoke token endpoint a successful response
    And I add "Authorization" header equal to "Bearer SomeTokenHere"
    When I send a POST request to "/merchant/bank-account" with body:
    """
      {"iban":"DE12500105171514412424", "tc_accepted":false}
    """
    Then the response status code should be 400
    And the JSON response should be:
    """
      {"errors":[
        {"title":"This is not a valid International Bank Account Number (IBAN).","code":"request_validation_error","source":"iban"},
        {"title":"This value should not be blank.","code":"request_validation_error","source":"tc_accepted"},
        {"title":"This value should be equal to true.","code":"request_validation_error","source":"tc_accepted"}
       ]}
    """

  Scenario: Failed authorised call to POST /merchant/bank-account when data is missing
    Given a merchant user exists with permission MANAGE_ONBOARDING
    And I get from Oauth service a valid user token
    And I get from Oauth service revoke token endpoint a successful response
    And I add "Authorization" header equal to "Bearer SomeTokenHere"
    When I send a POST request to "/merchant/bank-account" with body:
    """
      {}
    """
    Then the response status code should be 400
    And the JSON response should be:
    """
      {"errors":[
        {"title":"This value should not be blank.","code":"request_validation_error","source":"iban"},
        {"title":"This value should not be blank.","code":"request_validation_error","source":"tc_accepted"},
        {"title":"This value should be equal to true.","code":"request_validation_error","source":"tc_accepted"}]
       }
    """

  Scenario: Successful authorised call to POST /merchant/bank-account
    Given a merchant user exists with permission MANAGE_ONBOARDING
    And I get from Oauth service a valid user token
    And I get from Oauth service revoke token endpoint a successful response
    And I get from companies service get debtor response
    And I successfully create sepa B2B document
    And I get from files service a good response
    And I add "Authorization" header equal to "Bearer SomeTokenHere"
    And I get from BIC lookup service call to "/bankcodes/50010517.json" a response with status 200 and body:
    """
      {"bank_code":{"bic":"INGDDEFFXXX", "bank_name": "commerzz"}}
    """
    When I send a POST request to "/merchant/bank-account" with body:
    """
      {"iban":"DE42500105171514412424", "tc_accepted":true}
    """
    Then the response status code should be 204
    And the sepa mandate document should exist for merchant
