Feature: Sources are scanned for semantic compatibility
	In order to discover semantic compatability in sources
	As a user
	I must scan a source

	Scenario Outline: Scanning a single source file
		Given My current working directory is the package root directory
		And The file "<file>" exists with: 
		"""
		<content>
		"""
		When I run triage with argument "<file>"
		Then I should see "0 directories"
		And I should see "1 file"
		And I should see "<mime_type>"
		And I should see "<file_name>"
		And the exit status should be 0
		
		Examples:
			| file               | content          | mime_type                  | file_name  |
			|  var/tmp/test.txt  |  this is a test  |  text/plain                |  test.txt  |
			|  var/tmp/test.dat  |  a test this is  |  application/octet-stream  |  test.dat  |
			|  var/tmp/test.md   |  is this a test  |  text/x-markdown           |  test.md   |
	
	Scenario: Scanning a directory with multiple files
		Given My current working directory is the package root directory
		When I run triage with argument "tests/fixtures/picker-test/directory-with-20-files"
		Then I should see "1 directory"
		And I should see "20 files"
		And I should see "text/plain"
		And the exit status should be 0
		
	Scenario: Scanning a directory with subdirectories
		Given My current working directory is the package root directory
		When I run triage with argument "tests/fixtures/picker-test/directory-with-subdirectories-and-files"
		Then I should see "4 directories"
		And I should see "8 files"
		And I should see "text/plain"
		And the exit status should be 0		