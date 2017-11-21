<?php

namespace Machine\Tests;

require './vendor/autoload.php';

class AuthPluginTest extends \PHPUnit_Framework_TestCase
{
	private function _request($method, $path)
	{
		return [
			"SERVER" => [
				"REQUEST_METHOD" => $method,
				"REQUEST_URI" => $path,
        "SCRIPT_NAME" => "/index.php",
				"HTTP_HOST" => "localhost:8000",
				"REMOTE_ADDR" => "127.0.0.1"
			],
			"templates_path" => "tests/machine/templates/",
			"plugins_path" => "plugins/"
		];
	}
	
	public function testLogin() 
	{
		// ====================================================================
		//	1. login and setup auth cookie
		// ====================================================================
		// setup request and machine
		$req = $this->_request("POST", "/login/");
		$machine = new \Machine\Machine($req);
		
		// adding plugins
		$db = $machine->addPlugin("Database");	
		$auth = $machine->addPlugin("Auth");	
		
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
		$machine->addAction("/login/", "POST", function($machine) {
			$auth = $machine->plugin("Auth");
			
			// write credentials controls here...
			// ...
			
			// if ok, authenticate.
			$auth->generateAuthCookies(1);
			
			// then, redirect
			$machine->redirect("/dashboard/");
		}); 
		
		$response = $machine->run(true);
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
		$machine2 = new \Machine\Machine($req2);

		// adding plugins
		$machine2->addPlugin("Database");	
		$machine2->addPlugin("Auth");
	
		// setup plugins
		$machine2->plugin("Database")->setupSqlite("testdb");
		$machine2->plugin("Auth")->setDataCallback(function($machine, $user_id) {
			return $machine->plugin("Database")->load("user", $user_id);
		});
		
		$machine2->addPage("/dashboard/", function($machine) {
			$auth = $machine->plugin("Auth");
			$auth->checkLogin();
			return [
				"template" => "test.php",
				"data" => [
					"content" => "Logged user: " . $auth->data->name
				]
			];
		});
		$response = $machine2->run(true);
		$this->assertEquals("<h1>Logged user: John</h1>", $response["body"]);
		
		$db->close();
	}
}
