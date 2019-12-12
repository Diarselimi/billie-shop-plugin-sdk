Feature: As a merchant I should be able to move the technical integration to pending 

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "Authorization" header equal to "Bearer someToken"
    And I get from Oauth service a valid user token
    And a merchant user exists with permission MANAGE_ONBOARDING

  Scenario: Success to move the step to pending
    When I send a POST request to "/merchant/finish-integration"
    Then the response status code should be 204

  Scenario: Fail to move the step to pending if it's already in pending
    Given The following onboarding steps are in states for merchant "f2ec4d5e-79f4-40d6-b411-31174b6519ac":
      | name                  | state |
      |technical_integration  | confirmation_pending |
    When I send a POST request to "/merchant/finish-integration"
    Then the response status code should be 400

  Scenario: I succeed to move the step to pending because it's already in complete state
    Given The following onboarding steps are in states for merchant "f2ec4d5e-79f4-40d6-b411-31174b6519ac":
      | name                  | state     |
      |technical_integration  | complete  |
    When I send a POST request to "/merchant/finish-integration"
    Then the response status code should be 204
