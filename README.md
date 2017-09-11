# Installing

	composer require paooolino/machine:dev-master

# Usage

## index.php

   require("vendor/autoload.php");

   $machine = new \Machine\Machine();

   $machine->addPage("/", function() {
	   return [
		   template: "page.php",
		   data: [
			   "content" => "Hello, world!"
		   ]
	   ];
   });

   $machine->run();
   
## templates/default/page.php

	<h1>{{content}}</h1>
	
# Serving

	php -S locahost:8000
	
# For developers

**composer start**
	starts the php built-in server for localhost:8000 pointing to the example/ 
	directory
	
**composer phpunit**
	launch tests for the main Machine class and generate code coverage in coverage/machine

**composer phpunit_plugins**
	launch tests for plugins and generate code coverage in coverage/plugins
	
**composer generatedoc**
	generate docs in the doc/ directory

**composer phpcbf**
	auto-correct source code to match pear standards

**compose phpcs**
	check for errors in code standars
