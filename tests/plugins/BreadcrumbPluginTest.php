<?php

namespace Machine\Tests;

require './vendor/autoload.php';

class BreadcrumbPluginTest extends \PHPUnit_Framework_TestCase
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
	
	public function testBreadcrumb() 
	{
		$req = $this->_request("GET", "/");
		
		$machine = new \Machine\Machine($req);
		$machine->addPlugin("Breadcrumb");	
		$machine->addPage("/", function($machine) {
			$bc = $machine->plugin("Breadcrumb");
			$bc->add("Home", "/");
			$bc->add("Products", "/products/");
			$bc->setLabel("Woman skirt");
			return [
				"template" => "test.php",
				"data" => [
					"content" => "{{Breadcrumb|Render}}"
				]
			];
		});
		$response = $machine->run(true);
		
		$this->assertEquals('<h1><span><a href="/">Home</a></span> | <span><a href="/products/">Products</a></span> | Woman skirt</h1>', $response["body"]);
	}
	
}
