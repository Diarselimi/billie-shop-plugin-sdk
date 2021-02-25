Feature: An endpoint to return list of supported legal forms

  Background:
	Given I add "Content-type" header equal to "application/json"
	And I add "X-Test" header equal to 1

  Scenario: Successfully retrieve list of supported legal forms
	When I send a GET request to "/public/legal-forms"
	Then the response status code should be 200
	And the JSON response should be:
	"""
	{
	  "items": [
		{
		  "code": 10001,
		  "name": "GmbH (Gesellschaft mit beschränkter Haftung)",
		  "required_input": "HR-NR",
		  "required": 1
		},
		{
		  "code": 90001,
		  "name": "GmbH & Co. KG",
		  "required_input": "HR-NR",
		  "required": 1
		},
		{
		  "code": 6022,
		  "name": "Einzelunternehmer (ohne HR-Eintrag)",
		  "required_input": "Ust-ID",
		  "required": 1
		},
		{
		  "code": 6001,
		  "name": "Einzelfirma (im Handelsregister eingetragen)",
		  "required_input": "HR-NR",
		  "required": 1
		},
		{
		  "code": 3001,
		  "name": "Gewerbebetrieb",
		  "required_input": "Ust-ID",
		  "required": 1
		},
		{
		  "code": 4001,
		  "name": "GbR (Gesellschaft bürgerlichen Rechts)",
		  "required_input": "Ust-ID",
		  "required": 0
		},
		{
		  "code": 10201,
		  "name": "UG (Unternehmergesellschaft haftungsbeschränkt)",
		  "required_input": "HR-NR",
		  "required": 1
		},
		{
		  "code": 11001,
		  "name": "AG (Aktiengesellschaft)",
		  "required_input": "HR-NR",
		  "required": 1
		},
		{
		  "code": 90101,
		  "name": "AG & Co. KG",
		  "required_input": "HR-NR",
		  "required": 1
		},
		{
		  "code": 8001,
		  "name": "KG (Kommanditgesellschaft)",
		  "required_input": "HR-NR",
		  "required": 1
		},
		{
		  "code": 7001,
		  "name": "OHG (Offene Handelsgesellschaft)",
		  "required_input": "HR-NR",
		  "required": 1
		},
		{
		  "code": 18001,
		  "name": "Stiftung",
		  "required_input": "Ust-ID",
		  "required": 1
		},
		{
		  "code": 13001,
		  "name": "e.V. (eingetragener Verein)",
		  "required_input": "HR-NR",
		  "required": 0
		},
		{
		  "code": 12001,
		  "name": "e.G. (eingetragene Genossenschaft)",
		  "required_input": "HR-NR",
		  "required": 1
		},
		{
		  "code": 10101,
		  "name": "Ltd. (Limited)",
		  "required_input": "HR-NR",
		  "required": 1
		},
		{
				"code": 99998,
				"name": "Öffentliche Einrichtung",
				"required_input": "HR-NR",
				"required": 0
		},
		{
		  "code": 99999,
		  "name": "Sonstige",
		  "required_input": "HR-NR",
		  "required": 0
		}
	  ]
	}
	"""

  Scenario: Successfully retrieve legal forms with v1 api
    When I send a GET request to "/public/api/v1/legal-forms"
    Then the response status code should be 200

  Scenario: Successfully retrieve legal forms with v2 api
    When I send a GET request to "/public/api/v2/legal-forms"
    Then the response status code should be 200
