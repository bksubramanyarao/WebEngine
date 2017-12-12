<?php

namespace WebEngine\Tests;

require './vendor/autoload.php';

class AuthPluginTest extends \PHPUnit_Framework_TestCase
{
	private function _request($method, $path)
	{
		return [
			"SERVER" => [
				"REQUEST_METHOD" => $method,
				"REQUEST_URI" => $path,
        "DOCUMENT_ROOT" => "C:\www\example.com\httpdocs",
        "SCRIPT_FILENAME" => "C:\www\example.com\httpdocs/index.php",
				"HTTP_HOST" => "localhost:8000",
				"REMOTE_ADDR" => "127.0.0.1"
			],
			"templates_path" => "tests/engine/templates/",
			"plugins_path" => "plugins/"
		];
	}
	
	public function testLogin() 
	{
		// ====================================================================
		//	1. login and setup auth cookie
		// ====================================================================
		// setup request and engine
		$req = $this->_request("POST", "/login/");
		$engine = new \WebEngine\WebEngine($req);
		
		// adding plugins
		$db = $engine->addPlugin("Database");	
		$auth = $engine->addPlugin("Auth");	
		
		// setup plugins
		$db->setupSqlite("testdb");
		$db->nuke();
		$db->addItem("user", [
			"name" => "John"
		]);
		$db->addItem("user", [
			"name" => "Jane"
		]);
		
		// add login action
		$engine->addAction("/login/", "POST", function($engine) {
			$auth = $engine->plugin("Auth");
			
			// write credentials controls here...
			// ...
			
			// if ok, authenticate.
			$auth->generateAuthCookies(1);
			
			// then, redirect
			$engine->redirect("/dashboard/");
		}); 
		
		$response = $engine->run(true);
		$cookie_name = $response["cookies"][0][0];
		$cookie_value = $response["cookies"][0][1];
		
		// ====================================================================
		//	2. checklogin
		// ====================================================================
		// setup a new request
		$req2 = $this->_request("GET", "/dashboard/");
		$req2["COOKIE"] = [
			$cookie_name => $cookie_value
		];
		$engine2 = new \WebEngine\WebEngine($req2);

		// adding plugins
		$engine2->addPlugin("Database");	
		$engine2->addPlugin("Auth");
	
		// setup plugins
		$engine2->plugin("Database")->setupSqlite("testdb");
		$engine2->plugin("Auth")->setDataCallback(function($engine, $user_id) {
			return $engine->plugin("Database")->load("user", $user_id);
		});
		
		$engine2->addPage("/dashboard/", function($engine) {
			$auth = $engine->plugin("Auth");
			$auth->checkLogin();
			return [
				"template" => "test.php",
				"data" => [
					"content" => "Logged user: " . $auth->data->name
				]
			];
		});
		$response = $engine2->run(true);
		$this->assertEquals("<h1>Logged user: John</h1>", $response["body"]);
		
		$db->close();
	}
}
