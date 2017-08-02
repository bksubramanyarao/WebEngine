<?php

namespace Machine\Tests;

require './vendor/autoload.php';

class MachineTest extends \PHPUnit_Framework_TestCase
{
	private function _request($method, $path)
	{
		return [
			"SERVER" => [
				"REQUEST_METHOD" => $method,
				"REQUEST_URI" => $path
			],
			"templates_path" => "tests/templates/"
		];
	}
	
	public function testPageOk()
	{
		$homepage = $this->_request("GET", "/");
		
		$machine = new \Machine\Machine($homepage);
		$machine->addPage("/", function() {
			return [
				"template" => "test.php",
				"data" => [
					"content" => "Home page"
				]
			];
		});
		$response = $machine->run();
		$this->assertEquals("<h1>Home page</h1>", $response["output"]);
	}
}
