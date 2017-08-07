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
				"HTTP_HOST" => "localhost:8000"
			],
			"templates_path" => "tests/machine/templates/",
			"plugins_path" => "plugins/"
		];
	}
	
	public function testLogin() 
	{
		$req = $this->_request("GET", "/");
		
		$machine = new \Machine\Machine($req);
		$machine->addPlugin("Database");	
		$machine->addPlugin("Auth");	
		
		$db = $machine->plugin("Database");
		$db->setupSqlite("testdb");
		$db->nuke();
		
		$db->addItem("users", [
			"name" => "John"
		]);
		$db->addItem("users", [
			"name" => "Jane"
		]);
		
		$auth = $machine->plugin("Auth");
		$auth->setDataCallback(function($machine, $user_id) {
			return $machine->plugin("Database")->getItem("user", $user_id);
		});
		
		$auth->generateAuthCookies(1);
		$auth->checkLogin();
		
		$this->assertEquals(1, $auth->logged_user_id);
		$this->assertEquals("John", $auth->data["name"]);
	}
}
