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
				"HTTP_HOST" => "localhost:8000",
        "SCRIPT_NAME" => "/index.php"
			],
			"templates_path" => "tests/machine/templates/",
			"plugins_path" => "plugins/"
		];
	}
	
	public function testGet() 
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
		$response = $machine->run(true);
		$link = $machine->plugin("Link")->Get("/testlink/");
		
		$this->assertEquals("<h1>//localhost:8000/testlink/</h1>", $response["body"]);
		$this->assertEquals("//localhost:8000/testlink/", $link);	
	}
	
	public function testParametrizedGet()
	{
		$req = $this->_request("GET", "/");
		
		$machine = new \Machine\Machine($req);
		$Link = $machine->addPlugin("Link");	
		$Link->setRoute("LANGUAGE_PAGE", "/language/{lang}/{version}/");
		$the_link = $Link->Get(["LANGUAGE_PAGE", "php", "5"]);
		
		$this->assertEquals("//localhost:8000/language/php/5/", $the_link);
	}
	
	public function testActive()
	{
		$req = $this->_request("GET", "/");
		
		$machine = new \Machine\Machine($req);
		$machine->addPlugin("Link");	
		$machine->addPage("/", function($machine) {
			return [
				"template" => "test.php",
				"data" => [
					"content" => "<span>{{Link|Active|/}}</span><span>{{Link|Active|/contacts/}}</span>"
				]
			];
		});
		$response = $machine->run(true);
		
		$this->assertEquals("<h1><span>active</span><span></span></h1>", $response["body"]);
		$this->assertEquals("active", $machine->plugin("Link")->Active("/"));
		$this->assertEquals("active", $machine->plugin("Link")->Active(["/"]));
		$this->assertEquals("", $machine->plugin("Link")->Active("/contacts/"));
		$this->assertEquals("", $machine->plugin("Link")->Active(["/contacts/"]));
	}
	
	public function testSetName() 
	{
		$req = $this->_request("GET", "/contacts/");
		
		$machine = new \Machine\Machine($req);
		$Link = $machine->addPlugin("Link");
		$Link->setRoute("CONTACT_PAGE", "/contacts/");
		$machine->addPage($Link->getRoute("CONTACT_PAGE"), function($machine) {
			return [
				"template" => "test.php",
				"data" => [
					"content" => "{{Link|Get|CONTACT_PAGE}}"
				]
			];
		});
		
		$response = $machine->run(true);
		
		$this->assertEquals("<h1>//localhost:8000/contacts/</h1>", $response["body"]);
	}
}
