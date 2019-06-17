Feature:
    In order to check that the system is up and runnning
    I want to make request to the /healthcheck endpoint
    And receive back a static response

    Scenario: Health Check
        Given I send a GET request to "/healthcheck"
        Then the response status code should be 200
        And the response should contain "paella_core is alive"
