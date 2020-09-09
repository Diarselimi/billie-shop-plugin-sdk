Feature: Deactivate user

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1

  Scenario: Successfully deactivate user
    Given a merchant user exists with uuid "42e8bd74-22ac-4fec-b549-9bc01c353c12" and role admin and permission MANAGE_USERS
    And a merchant user exists with uuid "fed64fa2-d591-43ae-b3b9-5f758dcc57ae" and role developer and permission CREATE_ORDERS
    And a role none exists with uuid "299f98ef-cb67-4aab-9cc8-c86b7059073d"
    And I get from Oauth service a valid user token with user uuid "42e8bd74-22ac-4fec-b549-9bc01c353c12"
    And I add "Authorization" header equal to "Bearer SomeTokenHere"
    When I send a POST request to "/merchant/user/fed64fa2-d591-43ae-b3b9-5f758dcc57ae/deactivate"
    Then the response status code should be 204
    And print last JSON response
    And the user with uuid "fed64fa2-d591-43ae-b3b9-5f758dcc57ae" has role none

