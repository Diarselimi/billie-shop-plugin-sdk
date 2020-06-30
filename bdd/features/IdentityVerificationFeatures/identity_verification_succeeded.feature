Feature: Identity verification succeeded

	Scenario: Succeeded identification verification updates onboarding step
		Given The following onboarding steps are in states for merchant 1:
			| name                        | state                |
			| identity_verification       | confirmation_pending |
		And a merchant user exists with permission MANAGE_ONBOARDING and identity verification case uuid "aaa"
		When I consume an existing queue message of type identity.identity_verification_succeeded containing this payload:
    """
    {"case_uuid":"aaa"}
    """
		Then the onboarding step identity_verification should be in state complete for merchant 1
