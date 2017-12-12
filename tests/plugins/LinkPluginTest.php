<?php

namespace WebEngine\Tests;

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
        "DOCUMENT_ROOT" => "C:\www\example.com\httpdocs",
        "SCRIPT_FILENAME" => "C:\www\example.com\httpdocs/index.php"
			],
			"templates_path" => "tests/engine/templates/",
			"plugins_path" => "plugins/"
		];
	}
	
	public function testGet() 
	{
		$req = $this->_request("GET", "/");
		
		$engine = new \WebEngine\WebEngine($req);
		$engine->addPlugin("Link");	
		$engine->addPage("/", function($engine) {
			return [
				"template" => "test.php",
				"data" => [
					"content" => "{{Link|Get|/testlink/}}"
				]
			];
		});
		$response = $engine->run(true);
		$link = $engine->plugin("Link")->Get("/testlink/");
		
		$this->assertEquals("<h1>//localhost:8000/testlink/</h1>", $response["body"]);
		$this->assertEquals("//localhost:8000/testlink/", $link);	
	}
	
	public function testParametrizedGet()
	{
		$req = $this->_request("GET", "/");
		
		$engine = new \WebEngine\WebEngine($req);
		$Link = $engine->addPlugin("Link");	
		$Link->setRoute("LANGUAGE_PAGE", "/language/{lang}/{version}/");
		$the_link = $Link->Get(["LANGUAGE_PAGE", "php", "5"]);
		
		$this->assertEquals("//localhost:8000/language/php/5/", $the_link);
	}
	
	public function testActive()
	{
		$req = $this->_request("GET", "/");
		
		$engine = new \WebEngine\WebEngine($req);
		$engine->addPlugin("Link");	
		$engine->addPage("/", function($engine) {
			return [
				"template" => "test.php",
				"data" => [
					"content" => "<span>{{Link|Active|/}}</span><span>{{Link|Active|/contacts/}}</span>"
				]
			];
		});
		$response = $engine->run(true);
		
		$this->assertEquals("<h1><span>active</span><span></span></h1>", $response["body"]);
		$this->assertEquals("active", $engine->plugin("Link")->Active("/"));
		$this->assertEquals("active", $engine->plugin("Link")->Active(["/"]));
		$this->assertEquals("", $engine->plugin("Link")->Active("/contacts/"));
		$this->assertEquals("", $engine->plugin("Link")->Active(["/contacts/"]));
	}
	
	public function testSetName() 
	{
		$req = $this->_request("GET", "/contacts/");
		
		$engine = new \WebEngine\WebEngine($req);
		$Link = $engine->addPlugin("Link");
		$Link->setRoute("CONTACT_PAGE", "/contacts/");
		$engine->addPage($Link->getRoute("CONTACT_PAGE"), function($engine) {
			return [
				"template" => "test.php",
				"data" => [
					"content" => "{{Link|Get|CONTACT_PAGE}}"
				]
			];
		});
		
		$response = $engine->run(true);
		
		$this->assertEquals("<h1>//localhost:8000/contacts/</h1>", $response["body"]);
	}
}
