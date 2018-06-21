Feature: The version information is displayed to help the user know about the program version
	In order to know which version of the program I am using
	As a user
	I must see version information

	Scenario: Supplying the --version argument on the command line
		Given My current working directory is the package root directory
		When I run the command "bin/triage --version"
		Then I should see "triage"
		And I should see "Copyright (c) 2018 by the contributors"
		And I should see "The above copyright notice and this permission notice shall be included"
		And the exit status should be 0
