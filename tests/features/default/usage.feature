Feature: The usage information is displayed to help the user figure out command line usage
	In order to recover from missing or extra command line options
	As a user
	I must see usage information

	Scenario: Supplying no arguments on the command line
		Given My current working directory is the package root directory
		When I run the command "bin/triage"
		Then I should see "Usage: triage [OPTION]... SOURCE"
		And I should see "Try 'triage --help' for more information."
		And the exit status should be 1

	Scenario: Supplying too many arguments on the command line
		Given My current working directory is the package root directory
		When I run the command "bin/triage too many arguments"
		Then I should see "Usage: triage [OPTION]... SOURCE"
		And I should see "Try 'triage --help' for more information."
		And the exit status should be 1
