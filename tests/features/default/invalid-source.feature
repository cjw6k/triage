Feature: An error message is displayed to help the user recover from invalid arguments 
	In order to recover from invalid command line arguments
	As a user
	I must see relevant error information

	Scenario: Supplying invalid path for SOURCE on the command line
		Given My current working directory is the package root directory
		When I run the command "bin/triage 'invalid source path'"
		Then I should see "triage: no such file or directory"
		And the exit status should be 1	
		
	Scenario: Supplying non-existent path for SOURCE on the command line
		Given My current working directory is the package root directory
		And The directory 'not/a/real/path' does not exist
		When I run the command "bin/triage not/a/real/path"
		Then I should see "triage: no such file or directory"
		And the exit status should be 1
		
	Scenario: Supplying non-readable path for SOURCE on the command line
		Given My current working directory is the package root directory
		And The directory 'var/tmp/unreadable' exists
		And The directory 'var/tmp/unreadable' is not readable
		When I run the command "bin/triage var/tmp/unreadable"
		Then I should see "triage: cannot open 'var/tmp/unreadable' for reading: Permission denied"
		And the exit status should be 1
