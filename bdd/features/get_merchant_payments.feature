Feature:
    In order to retrieve the merchant payments

    Background:
        Given I add "Content-type" header equal to "application/json"
        And I add "X-Test" header equal to 1
        And I add "X-Api-Key" header equal to test

    Scenario: Get merchant payments details, extended for support
        When I send a GET request to "/public/payments"
        Then the response status code should be 200

    Scenario: Get payment with sorting params
        When I send a GET request to "/public/payments?sort_by=transaction_date&sort_direction=asc"
        Then the response status code should be 200

    Scenario: Get payment list, sort and search by external_id
        When I send a GET request to "/public/payments?sort_by=transaction_date&sort_direction=asc&external_id=DE123SA"
        Then the response status code should be 200

    Scenario: Get payment list by search with search and limit
        When I send a GET request to "/public/payments?search=test&limit=5"
        Then the response status code should be 200

    Scenario: I fail to get payment list by payment_debtor_uuid which is not valid
        When I send a GET request to "/public/payments?merchant_debtor_uuid=not_valid_uuid"
        Then the response status code should be 400

    Scenario: I fail to get payments resutls if I search with invalid transaction_uuid
        When I send a GET request to "/public/payments?transaction_uuid=1234-1234-1234-12344not_valid"
        Then the response status code should be 400




