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
		$req = $this->_request("GET", "/");
		
		$machine = new \Machine\Machine($req);
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
	
	public function testActionOk()
	{
		$req = $this->_request("POST", "/actionpost/");
		
		$machine = new \Machine\Machine($req);
		$machine->addAction("/actionpost/", "POST", function($machine) {
			// action code
		});
		$response = $machine->run();
		$this->assertEquals("Callback redirect missing.", $response["ERROR"]);
	}
	
	public function testMethodNotFoundOk()
	{
		$req = $this->_request("POST", "/actionpost/");
		
		$machine = new \Machine\Machine($req);
		$machine->addAction("/actionpost/", "GET", function($machine) {
			// action code
		});
		$response = $machine->run();
		$this->assertEquals("No route found.", $response["ERROR"]);
	}
	
	public function testRouteNotFound()
	{
		$req = $this->_request("GET", "/non-existent-page/");
		
		$machine = new \Machine\Machine($req);
		$machine->addPage("/", function() {
			return [
				"template" => "test.php",
				"data" => [
					"content" => "Home page"
				]
			];
		});
		$response = $machine->run();
		$this->assertEquals("No route found.", $response["ERROR"]);
	}
	
	public function testTemplateNotFound()
	{
		$req = $this->_request("GET", "/");
		
		$machine = new \Machine\Machine($req);
		$machine->addPage("/", function() {
			return [
				"template" => "non-existent-template.php",
				"data" => [
					"content" => "Home page"
				]
			];
		});
		$response = $machine->run();
		$this->assertEquals("Missing template file: "
			. "tests/templates/non-existent-template.php", $response["output"]);
	}
	
	public function testRouteDuplicated()
	{
		$req = $this->_request("GET", "/");
		
		$machine = new \Machine\Machine($req);
		$machine->addPage("/", function() {
			//
		});
		
		$result = $machine->addPage("/duplicated/", function() {
			//
		});
		$this->assertEquals("", $result);
		
		$result = $machine->addPage("/duplicated/", function() {
			//
		});
		$this->assertEquals("Config Error: duplicated route. Route exists "
			. "for GET method (/duplicated/)", $result);
	}
}
