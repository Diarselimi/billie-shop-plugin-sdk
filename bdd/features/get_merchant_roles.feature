Feature: Retrieve all merchant roles (APIS-1433 + APIS-1497)

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
	And I add "Authorization" header equal to "Bearer someToken"
	And I get from Oauth service a valid user token

  Scenario: Successfully retrieve roles
	Given a merchant user exists with role "editor" and permission MANAGE_USERS
    When I send a GET request to "/public/merchant/roles"
    Then the response status code should be 200
    And the JSON response should be:
	"""
	  [
	    {"uuid":"editor_uuid", "name":"editor"}
	  ]
	"""

  Scenario: Retrieve roles fails with wrong permissions
	Given a merchant user exists with permission VIEW_USERS
    When I send a GET request to "/public/merchant/roles"
    Then the response status code should be 403
    And the JSON response should be:
	"""
	  {"errors":[{"title":"Access Denied.","code":"forbidden"}]}
	"""
