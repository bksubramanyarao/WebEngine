Usage
=====
composer start
	starts the php built-in server for localhost:8000 pointing to the example/ 
	directory
	
composer phpunit
	launch tests for the main Machine class and generate code coverage in coverage/machine

composer phpunit_plugins
	launch tests for plugins and generate code coverage in coverage/plugins
	
composer generatedoc
	generate docs in the doc/ directory

composer phpcbf
	auto-correct source code to match pear standards

compose phpcs
	check for errors in code standars
