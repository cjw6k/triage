Feature: The usage information is displayed to help the user figure out command line usage
	In order to recover from missing command line options
	As a user
	I muse see usage information

	Scenario: Supplying no arguments on the command line
		Given I run the command "bin/triage"
		Then I should see "usage"
		And the exit status should be 1
