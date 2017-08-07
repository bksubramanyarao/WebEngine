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
				"REQUEST_URI" => $path,
				"HTTP_HOST" => "localhost:8000"
			],
			"templates_path" => "tests/machine/templates/",
			"plugins_path" => "tests/machine/plugins/"
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
		$response = $machine->run(true);
		$this->assertEquals("<h1>Home page</h1>", $response["body"]);
	}
	
	public function testSetTemplate()
	{
		$req = $this->_request("GET", "/");
		
		$machine = new \Machine\Machine($req);
		$machine->setTemplate("testtemplate");
		$machine->addPage("/", function() {
			return [
				"template" => "test.php",
				"data" => [
					"content" => "Home page"
				]
			];
		});
		$response = $machine->run(true);
		$this->assertEquals("<h1>TEST TEMPLATE Home page</h1>", $response["body"]);
	}
	
	public function testRouteParams()
	{
		$req = $this->_request("GET", "/languages/php/5/");
		
		$machine = new \Machine\Machine($req);
		$machine->addPage("/languages/{language}/{version}/", function($machine, $language, $version) {
			$this->assertEquals("Machine\Machine", get_class($machine));
			$this->assertEquals("php", $language);
			$this->assertEquals("5", $version);
		});
		$response = $machine->run(true);		
	}
	
	public function testActionOk()
	{
		$req = $this->_request("POST", "/actionpost/");
		
		$machine = new \Machine\Machine($req);
		$machine->addAction("/actionpost/", "POST", function($machine) {
			// action code
			$machine->redirect("/landing/");
		});
		$response = $machine->run(true);
		$headers = $response["headers"];
		$this->assertEquals(1, count($response["headers"]));
		$this->assertEquals("location: /landing/", $response["headers"][0]);
	}
	
	public function testMethodNotFoundOk()
	{
		$req = $this->_request("POST", "/actionpost/");
		
		$machine = new \Machine\Machine($req);
		$machine->addAction("/actionpost/", "GET", function($machine) {
			// action code
		});
		$response = $machine->run(true);
		$this->assertEquals(404, $response["code"]);
		$this->assertEquals("Not found", $response["reason"]);
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
		$response = $machine->run(true);
		$this->assertEquals(404, $response["code"]);
		$this->assertEquals("Not found", $response["reason"]);
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
		$response = $machine->run(true);
		$this->assertEquals("Missing template file: "
			. "tests/machine/templates/default/non-existent-template.php", $response["body"]);
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
	
	public function testAddPlugin() 
	{
		$req = $this->_request("GET", "/");
		
		$machine = new \Machine\Machine($req);
		$machine->addPlugin("Sample");
		$this->assertEquals("Plugin\Sample", get_class($machine->plugin("Sample")));
	}
	
	public function testAddNonExistentPlugin() 
	{
		$req = $this->_request("GET", "/");
		
		$machine = new \Machine\Machine($req);
		$result = $machine->addPlugin("NonExistent");
		$this->assertEquals("Unable to find tests/machine/plugins/NonExistent.php", $result);
	}
	
	public function testUsePlugin() 
	{
		$req = $this->_request("GET", "/");
		
		$machine = new \Machine\Machine($req);
		$machine->addPlugin("Sample");
		$machine->addPage("/", function() {
			return [
				"template" => "testplug.php",
				"data" => [
					"content" => "{{Sample|Plugfun|par1|par2|par3}}"
				]
			];
		});
		$response = $machine->run(true);
		
		// {{Sample|plugfun|par1|par2|par3}}
		$this->assertContains(
			"<p>Sample plugin function called with params par1, par2, par3</p>", 
			$response["body"]
		);
		// echo $Sample->plugFun("test1");
		$this->assertContains(
			"<p>Sample plugin function called with params test1</p>", 
			$response["body"]
		);
		// echo $Sample->plugFun(["test2"]);
		$this->assertContains(
			"<p>Sample plugin function called with params test2</p>", 
			$response["body"]
		);
		// echo $Sample->plugFun(["par4", "par5"]);
		$this->assertContains(
			"<p>Sample plugin function called with params par4, par5</p>", 
			$response["body"]
		);

		$result = $machine->plugin("Sample")->Plugfun("test");
		$this->assertEquals("Sample plugin function called with params test", $result);
	}
	
	public function testTemplateTag()
	{
		$req = $this->_request("GET", "/");
		
		$machine = new \Machine\Machine($req);
		$machine->addPage("/", function() {
			return [
				"template" => "test.php",
				"data" => [
					"content" => "{{templatePath}}"
				]
			];
		});
		$response = $machine->run(true);
		$this->assertEquals("<h1>//localhost:8000/tests/machine/templates/default/</h1>", $response["body"]);
	}
	
	public function testGetRequest()
	{
		$req = $this->_request("GET", "/");
		
		$machine = new \Machine\Machine($req);
		$r = $machine->getRequest();
		
		$this->assertEquals("GET", $r["SERVER"]["REQUEST_METHOD"]);
		$this->assertEquals("/", $r["SERVER"]["REQUEST_URI"]);
		$this->assertEquals("localhost:8000", $r["SERVER"]["HTTP_HOST"]);
	}
	
	public function testSetCookie()
	{
		$req = $this->_request("POST", "/login/");
		$machine = new \Machine\Machine($req);
		$machine->addAction("/login/", "POST", function($machine) {
			$machine->setCookie("loggedIn", 1);
		});
		$response = $machine->run(true);
		$this->assertEquals(1, $response["cookies"][0][1]);		
	}
}