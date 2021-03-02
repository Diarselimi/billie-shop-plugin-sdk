Feature: Get Merchant sepa b2b mandate document

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "Authorization" header equal to "Bearer someToken"
    And I get from Oauth service a valid user token
    And a merchant user exists with permission VIEW_ONBOARDING

  Scenario: I try to get the SEPA B2B document while there is no document for merchant

  Scenario: I successfully get the SEPA B2B document with the correct headers
    Given I get from files service existing file content
    And I add "Authorization" header equal to "Bearer SomeTokenHere"
    When I send a GET request to "/public/merchant/bank-account/sepa-mandate-document"
    Then the response status code should be 200
