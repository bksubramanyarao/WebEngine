<?php

namespace WebEngine\Tests;

require './vendor/autoload.php';

class BreadcrumbPluginTest extends \PHPUnit_Framework_TestCase
{
	private function _request($method, $path)
	{
		return [
			"SERVER" => [
				"REQUEST_METHOD" => $method,
				"REQUEST_URI" => $path,
        "DOCUMENT_ROOT" => "C:\www\example.com\httpdocs",
        "SCRIPT_FILENAME" => "C:\www\example.com\httpdocs/index.php",
				"HTTP_HOST" => "localhost:8000"
			],
			"templates_path" => "tests/engine/templates/",
			"plugins_path" => "plugins/"
		];
	}
	
	public function testBreadcrumb() 
	{
		$req = $this->_request("GET", "/");
		
		$engine = new \WebEngine\WebEngine($req);
		$engine->addPlugin("Breadcrumb");	
		$engine->addPage("/", function($engine) {
			$bc = $engine->plugin("Breadcrumb");
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
		$response = $engine->run(true);
		
		$this->assertEquals('<h1><span><a href="/">Home</a></span> | <span><a href="/products/">Products</a></span> | Woman skirt</h1>', $response["body"]);
	}
	
}
