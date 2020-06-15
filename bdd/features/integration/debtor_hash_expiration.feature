@integration
Feature: Debtor hash should not be used if expired

	Background:
		Given I get a successful OAuth client creation response
		And I get from limit service create default debtor-customer limit successful response
		And I have an active merchant

	Scenario: When debtor hash is not expired, it should be used for known customer
		Given I have an order with state "declined" and debtor external ID "ABC-123", created at "-30 minute now"
		Then finding existing debtor external data should give 1 results when max minutes is set to 60

	Scenario: When debtor hash is expired, it should not be used for known customer
		Given I have an order with state "declined" and debtor external ID "ABC-123", created at "-4 hour now"
		Then finding existing debtor external data should give 0 results when max minutes is set to 60
