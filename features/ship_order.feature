Feature:
    In order to ship an order
    I want to have an end point to ship my orders
    And expect empty response

    Scenario: Successful order shipment
        Given I add "Content-type" header equal to "application/json"
        And I add "X-Test" header equal to 1
        And I add "X-Api-User" header equal to 1
        And I have a created order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
        And I start borscht
        When I send a POST request to "/order/CO123/ship"
        Then print last response
        Then the response status code should be 204
        And the response should be empty
        And the order "CO123" is shipped
