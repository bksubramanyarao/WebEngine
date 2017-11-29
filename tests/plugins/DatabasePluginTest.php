<?php

namespace Machine\Tests;

require './vendor/autoload.php';

class DatabasePluginTest extends \PHPUnit_Framework_TestCase
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
			"templates_path" => "tests/machine/templates/",
			"plugins_path" => "plugins/"
		];
	}
	
	public function testCrud() 
	{
		$req = $this->_request("GET", "/");
		
		$machine = new \Machine\Machine($req);
		$machine->addPlugin("Database");	
		$response = $machine->run(true);
		
		$db = $machine->plugin("Database");
		$db->setupSqlite("testdb");
		$db->nuke();
		
		$id1 = $db->addItem("tabletest", [
			"name" => "John",
			"surname" => "Doe",
			"age" => 39,
			"birthdate" => "1978-04-29 12:10:45",
			"active" => true
		]);
		$id2 = $db->addItem("tabletest", [
			"name" => "Jane",
			"surname" => "Doe",
			"age" => 21,
			"active" => false
		]);
		$this->assertEquals($id1, 1);
		$this->assertEquals($id2, 2);
		
		$john = $db->load("tabletest", 1);
		$this->assertEquals(1, $john->id);	
		$this->assertEquals("John", $john->name);	
		$this->assertEquals(1, $john->active);	
		$this->assertEquals(39, $john->age);	
		$this->assertEquals("1978-04-29 12:10:45", $john->birthdate);

		$items = $db->findAll("tabletest");
		$this->assertEquals(2, count($items));
		
		$actived = $db->find("tabletest", "active = 1");
		$this->assertEquals(1, count($actived));
		
		$jane = $db->load("tabletest", 2);
		$jane->active = true;
		$db->update($jane);
		$actived = $db->find("tabletest", "active = 1");
		$this->assertEquals(2, count($actived));
		
		$names = $db->getDistinctValues("tabletest", "name");
		$surnames = $db->getDistinctValues("tabletest", "surname");
		$ages = $db->getDistinctValues("tabletest", "age");
		$this->assertEquals(["Jane", "John"], $names);
		$this->assertEquals(["Doe"], $surnames);
		$this->assertEquals([21, 39], $ages);
		
		$db->close();
	}
}
