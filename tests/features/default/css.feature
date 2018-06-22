Feature: CSS sources are analyzed for semantic compatibility
	In order to discover semantic compatability in CSS sources
	As a user
	I must scan css sources
	
	Scenario Outline: Scanning a simple CSS file
		Given My current working directory is the package root directory
		And The file "var/tmp/test.css" exists with: 
		"""
		<content>
		"""
		When I run triage with argument "var/tmp/test.css"
		Then I should see "text/css"
		And I should see "Selectors: 1"
		And I should see "Warnings: <warnings>"
		And I should see "Errors: <errors>"
		
		Examples:
			| content                  | warnings | errors |
			|  div{color: blue}        |  0       |  0     |
			|  div::after{color: blue} |  0       |  0     |
			|  dv{color: blue}         |  0       |  1     |
			|  div:after{color: blue}  |  1       |  0     |
			|  dv:after{color: blue}   |  1       |  1     |
		