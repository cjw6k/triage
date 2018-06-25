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
		And I should see "Selectors: <selectors>"
		And I should see "Syntax:"
		And I should see "Notices: <notices>"
		And I should see "Warnings: <warnings>"
		And I should see "Errors: <errors>"
		And I should see "Semantics:"
		And I should see "POSH: <posh>"
		
		Examples:
			| content                         | selectors | notices | warnings | errors | posh |
                                             
			# Empty selector                  
			|                                 |  0        |  0      |  0       |  0     |  0   |
                                                                    
			# Elements                                              
			|  div{top: 0}                    |  1        |  0      |  0       |  0     |  0   |
			|  div{top: 0} a{top: 0}          |  2        |  0      |  0       |  0     |  0   |
			|  dv{top: 0}                     |  1        |  0      |  0       |  0     |  1   |
			|  dv{top: 0} a{top: 0}           |  2        |  0      |  0       |  0     |  1   |
			|  dv{top: 0} ab{top: 0}          |  2        |  0      |  0       |  0     |  2   |
			|  dv dv{top: 0}                  |  1        |  0      |  0       |  0     |  2   |
                                                                    
			# Classes                                               
			|  .foo{top: 0} 	              |  1        |  0      |  0       |  0     |  0   |
			|  .foo .bar {top: 0}             |  1        |  0      |  0       |  0     |  0   |
			|  .foo.bar {top: 0}              |  1        |  0      |  0       |  0     |  0   |
			                                                        
			# Ids                                                   
			|  #foo{top: 0} 	              |  1        |  0      |  0       |  0     |  0   |
			|  #foo #bar {top: 0}             |  1        |  0      |  0       |  0     |  0   |
                                                                    
			# Pseudo-elements                                       
			|  a::after{top: 0}               |  1        |  0      |  0       |  0     |  0   |
			|  a::before{top: 0}              |  1        |  0      |  0       |  0     |  0   |
			|  a::cue{top: 0}                 |  1        |  0      |  0       |  0     |  0   |
			|  a::first-letter{top: 0}        |  1        |  0      |  0       |  0     |  0   |
			|  a::first-line{top: 0}          |  1        |  0      |  0       |  0     |  0   |
			|  a::selection{top: 0}           |  1        |  0      |  0       |  0     |  0   |
			|  a::slotted{top: 0}             |  1        |  0      |  0       |  0     |  0   |
                                                                   
			# Experimental Psuedo-elements (as of 2018-06-24)      
			|  a::backdrop{top: 0}            |  1        |  1      |  0       |  0     |  0   |
			|  a::placeholder{top: 0}         |  1        |  1      |  0       |  0     |  0   |
			|  a::marker{top: 0}              |  1        |  1      |  0       |  0     |  0   |
			|  a::spelling-error{top: 0}      |  1        |  1      |  0       |  0     |  0   |
			|  a::grammar-error{top: 0}       |  1        |  1      |  0       |  0     |  0   |
			
			# Pseudo-classes
			|  a:active{top: 0}               |  1        |  0      |  0       |  0     |  0   |
			|  input:checked{top: 0}          |  1        |  0      |  0       |  0     |  0   |
			|  input:default{top: 0}          |  1        |  0      |  0       |  0     |  0   |
			|  :defined{top: 0}               |  1        |  0      |  0       |  0     |  0   |
			|  input:disabled{top: 0}         |  1        |  0      |  0       |  0     |  0   |
			|  a:empty{top: 0}                |  1        |  0      |  0       |  0     |  0   |
			|  input:enabled{top: 0}          |  1        |  0      |  0       |  0     |  0   |
			|  :first{top: 0}                 |  1        |  0      |  0       |  0     |  0   |
			|  a:first-child{top: 0}          |  1        |  0      |  0       |  0     |  0   |
			|  a:first-of-type{top: 0}        |  1        |  0      |  0       |  0     |  0   |
			|  a:focus{top: 0}                |  1        |  0      |  0       |  0     |  0   |
			|  :host{top: 0}                  |  1        |  0      |  0       |  0     |  0   |
			|  a:hover{top: 0}                |  1        |  0      |  0       |  0     |  0   |
			|  input:indeterminate{top: 0}    |  1        |  0      |  0       |  0     |  0   |
			|  input:in-range{top: 0}         |  1        |  0      |  0       |  0     |  0   |
			|  input:invalid{top: 0}          |  1        |  0      |  0       |  0     |  0   |
			|  a:lang(en){top: 0}             |  1        |  0      |  0       |  0     |  0   |
			|  a:last-child{top: 0}           |  1        |  0      |  0       |  0     |  0   |
			|  a:last-of-type{top: 0}         |  1        |  0      |  0       |  0     |  0   |
			|  :left{top: 0}                  |  1        |  0      |  0       |  0     |  0   |
			|  a:link{top: 0}                 |  1        |  0      |  0       |  0     |  0   |
			|  :not(a){top: 0}                |  1        |  0      |  0       |  0     |  0   |
			|  a:nth-child(2n+1){top: 0}      |  1        |  0      |  0       |  0     |  0   |
			|  a:nth-last-child(2n){top: 0}   |  1        |  0      |  0       |  0     |  0   |
			|  a:nth-last-of-type(3){top: 0}  |  1        |  0      |  0       |  0     |  0   |
			|  a:nth-of-type(3n+2){top: 0}    |  1        |  0      |  0       |  0     |  0   |
			|  a:only-child{top: 0}           |  1        |  0      |  0       |  0     |  0   |
			|  a:only-of-type{top: 0}         |  1        |  0      |  0       |  0     |  0   |
			|  input:optional{top: 0}         |  1        |  0      |  0       |  0     |  0   |
			|  input:out-of-range{top: 0}     |  1        |  0      |  0       |  0     |  0   |
			|  input:read-only{top: 0}        |  1        |  0      |  0       |  0     |  0   |
			|  input:read-write{top: 0}       |  1        |  0      |  0       |  0     |  0   |
			|  input:required{top: 0}         |  1        |  0      |  0       |  0     |  0   |
			|  :right{top: 0}                 |  1        |  0      |  0       |  0     |  0   |
			|  :root{top: 0}                  |  1        |  0      |  0       |  0     |  0   |
			|  :scope{top: 0}                 |  1        |  0      |  0       |  0     |  0   |
			|  :target{top: 0}                |  1        |  0      |  0       |  0     |  0   |
			|  input:valid{top: 0}            |  1        |  0      |  0       |  0     |  0   |
			|  a:visited{top: 0}              |  1        |  0      |  0       |  0     |  0   |

			# Experimental Pseudo-classes (as of 2018-06-24)
			|  a:any-link{top: 0}             |  1        |  1      |  0       |  0     |  0   |
			|  a:dir(rtl){top: 0}             |  1        |  1      |  0       |  0     |  0   |
			|  div:fullscreen{top: 0}         |  1        |  1      |  0       |  0     |  0   |
			|  :host(.foo){top: 0}            |  1        |  1      |  0       |  0     |  0   |
			|  :host-context(a){top: 0}       |  1        |  1      |  0       |  0     |  0   |

	Scenario Outline: Scanning a CSS file with syntax warnings
		Given My current working directory is the package root directory
		And The file "var/tmp/test.css" exists with: 
		"""
		<content>
		"""
		When I run triage with argument "var/tmp/test.css"
		Then I should see "text/css"
		And I should see "Selectors: <selectors>"
		And I should see "Syntax:"
		And I should see "Notices: <notices>"
		And I should see "Warnings: <warnings>"
		And I should see "Errors: <errors>"
		And I should see "Semantics:"
		And I should see "POSH: <posh>"
		
		Examples:
			| content                          | selectors | notices | warnings | errors | posh |
			
			# Invalid Pseudo-class parameters
			|  a:not(){top: 0}                 |  1        |  0      |  1       |  0     |  0   |
			|  a:nth-child(.foo){top: 0}       |  1        |  0      |  1       |  0     |  0   |
			|  a:nth-last-child(#foo){top: 0}  |  1        |  0      |  1       |  0     |  0   |
			                                                     
			# Pseudo-class syntax for pseudo-elements            
			|  div:after{top: 0}               |  1        |  0      |  1       |  0     |  0   |
			                                                     
			# Pseudo-element syntax for pseudo-classes           
			|  div::hover{top: 0}              |  1        |  0      |  1       |  0     |  0   |
			
			# The next selector includes a zero width space before the 'a'
			#  You may not see it, but it is there, thus the warning
			|  ​a{top: 0}       		           |  1        |  0      |  1       |  0     |  0   |
			
			# Miscellaneous
			|  a:not('.foo'){top: 0}           |  1        |  0      |  1       |  0     |  0   |
			|  a:nth-child(0){top: 0}          |  1        |  0      |  1       |  0     |  0   |			
			
	Scenario Outline: Scanning a CSS file with syntax errors
		Given My current working directory is the package root directory
		And The file "var/tmp/test.css" exists with: 
		"""
		<content>
		"""
		When I run triage with argument "var/tmp/test.css"
		Then I should see "text/css"
		And I should see "Selectors: <selectors>"
		And I should see "Syntax:"
		And I should see "Notices: <notices>"
		And I should see "Warnings: <warnings>"
		And I should see "Errors: <errors>"
		And I should see "Semantics:"
		And I should see "POSH: <posh>"
		
		Examples:
			| content                       | selectors | notices | warnings | errors | posh |
			
			# Invalid Elements
			|  -{top: 0}                    |  1        |  0      |  0       |  1     |  0   |
			
			# Invalid Classes
			|  .{top: 0}                   |  1        |  0      |  0       |  1     |  0   |			
			
			# Invalid Id
			|  #{top: 0}                   |  1        |  0      |  0       |  1     |  0   |
			                                                      
			# Invalid Pseudo-elements                             
			|  div::-{top: 0}               |  1        |  0      |  0       |  1     |  0   |
			|  div::foo{top: 0}             |  1        |  0      |  0       |  1     |  0   |
			|  div::after::foo{top: 0}      |  1        |  0      |  0       |  1     |  0   |
			                                                      
			# Invalid Pseudo-classes                              
			|  a:-{top: 0}                  |  1        |  0      |  0       |  1     |  0   |
			|  a:foo{top: 0}                |  1        |  0      |  0       |  1     |  0   |
			|  div:foo:bar(a){top: 0}       |  1        |  0      |  0       |  1     |  0   |
			|  a:foo:bar{top: 0}            |  1        |  0      |  0       |  1     |  0   |	

	Scenario Outline: Scanning a CSS file with noteworthy usage patterns
		Given My current working directory is the package root directory
		And The file "var/tmp/test.css" exists with: 
		"""
		<content>
		"""
		When I run triage with argument "var/tmp/test.css"
		Then I should see "text/css"
		And I should see "Selectors: <selectors>"
		And I should see "Syntax:"
		And I should see "Notices: <notices>"
		And I should see "Warnings: <warnings>"
		And I should see "Errors: <errors>"
		And I should see "Semantics:"
		And I should see "POSH: <posh>"
		
		Examples:
			| content                 | selectors | notices | warnings | errors | posh |

			# Vendor Extensions       
			|  ::-moz-foo{top: 0}     |  1        |  1      |  0       |  0     |  0   |
			|  ::-webkit-foo{top: 0}  |  1        |  1      |  0       |  0     |  0   |
			|  ::-o-foo{top: 0}       |  1        |  1      |  0       |  0     |  0   |
			|  ::-ms-foo{top: 0}      |  1        |  1      |  0       |  0     |  0   |		
			|  :-moz-foo{top: 0}      |  1        |  1      |  0       |  0     |  0   |
			|  :-webkit-foo{top: 0}   |  1        |  1      |  0       |  0     |  0   |
			|  :-o-foo{top: 0}        |  1        |  1      |  0       |  0     |  0   |
			|  :-ms-foo{top: 0}       |  1        |  1      |  0       |  0     |  0   |			