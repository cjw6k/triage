Feature: The help information is displayed to provide a listing of all the command line options
	In order to learn the triage command
	As a user
	I must see help information

	Scenario: Supplying --help argument on the command line
		Given My current working directory is the package root directory
		When I run the command "bin/triage --help"
		Then I should see "Usage: triage [OPTION]... SOURCE"
		And I should see "triage is a utility which checks webapp sources"
		And I should see "for compatibility with microformats semantics"
		And I should see "OPTIONS:"
		And I should see "	--help		display this help and exit"
		And I should see "	--version	output version information and exit"
		And I should see "EXAMPLES:"
		And I should see "	triage style.css"
		And I should see "	triage /var/www/wordpress"
		And the exit status should be 0

	Scenario: Supplying --help argument with other valid arguments on the command line 
		Given My current working directory is the package root directory
		When I run the command "bin/triage --help --version composer.json"
		Then I should see "EXAMPLES"
		And I should not see "Copyright"
		And the exit status should be 0	
		
	Scenario: Supplying --help argument with invalid arguments on the command line 
		Given My current working directory is the package root directory
		When I run the command "bin/triage not-a-file.test --help"
		Then I should see "EXAMPLES"
		And I should not see "no such file or directory"
		And the exit status should be 0
		
