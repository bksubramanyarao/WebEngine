<?php

namespace Machine\Tests;

require './vendor/autoload.php';

class LinkPluginTest extends \PHPUnit_Framework_TestCase
{
	private function _request($method, $path)
	{
		return [
			"SERVER" => [
				"REQUEST_METHOD" => $method,
				"REQUEST_URI" => $path,
				"HTTP_HOST" => "localhost:8000"
			],
			"templates_path" => "tests/machine/templates/",
			"plugins_path" => "plugins/"
		];
	}
	
	public function testUsePlugin() 
	{
		$req = $this->_request("GET", "/");
		
		$machine = new \Machine\Machine($req);
		$machine->addPlugin("Link");
		$machine->addPage("/", function($machine) {
			return [
				"template" => "test.php",
				"data" => [
					"content" => "{{Link|Get|/testlink/}}"
				]
			];
		});
		$response = $machine->run();
		
		$this->assertEquals("<h1>//localhost:8000/testlink/</h1>", $response["output"]);
		
		$link = $machine->plugin("Link")->Get("/testlink/");
		$this->assertEquals("//localhost:8000/testlink/", $link);	
	}
}
