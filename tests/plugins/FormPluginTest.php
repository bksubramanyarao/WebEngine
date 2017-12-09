<?php

namespace Machine\Tests;

require './vendor/autoload.php';

class FormPluginTest extends \PHPUnit_Framework_TestCase
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
  
  public function testAddForm()
  {
    $req = $this->_request("GET", "/");
    $machine = new \Machine\Machine($req);
    $machine->addPlugin("Form");	
    
		$machine->addPage("/", function($machine) {
			$Form = $machine->plugin("Form");
			$Form->addForm("myForm", [
        "action" => "/register/",
        "submitlabel" => "Invia",
        "fields" => [
          ["email", "text", ["name" => "email"]],
          ["password", "password", ["name" => "password"]]
        ]
      ]);
			return [
				"template" => "test.php",
				"data" => [
					"content" => "{{Form|Render|myForm}}"
				]
			];
		});
    
    $response = $machine->run(true);

    $this->assertContains('<form method="post" action="/register/" enctype="multipart/form-data">', $response["body"]);
    $this->assertContains('<button type="submit">Invia</button>', $response["body"]);
    
    $this->assertContains('<div class="formRow typetext">', $response["body"]);
    $this->assertContains('<input id="myFormemail" type="text" value="" name="email" />', $response["body"]);
    
    $this->assertContains('<div class="formRow typepassword">', $response["body"]);
    $this->assertContains('<input id="myFormpassword" type="password" name="password" />', $response["body"]);
  }
  
  public function testSetValues()
  {
    $req = $this->_request("GET", "/");
    $machine = new \Machine\Machine($req);
    $machine->addPlugin("Form");	
    
		$machine->addPage("/", function($machine) {
			$Form = $machine->plugin("Form");
			$Form->addForm("myForm", [
        "action" => "/register/",
        "submitlabel" => "Invia",
        "fields" => [
          ["email", "text", ["name" => "email"]],
          ["active", "checkbox", ["name" => "active"]]
        ]
      ]);
      $Form->setValues("myForm", [
        "email" => "test@test.it",
        "active" => 1,
      ]);
			return [
				"template" => "test.php",
				"data" => [
					"content" => "{{Form|Render|myForm}}"
				]
			];
		});
    
    $response = $machine->run(true);
    $this->assertContains('<input id="myFormemail" type="text" value="test@test.it" name="email" />', $response["body"]);
    $this->assertContains('<input id="myFormactive" type="checkbox" name="active" checked="checked" />', $response["body"]);
  }
  
  public function testSetFieldTemplate()
  {
    $req = $this->_request("GET", "/");
    $machine = new \Machine\Machine($req);
    $machine->addPlugin("Form");	
    
		$machine->addPage("/", function($machine) {
			$Form = $machine->plugin("Form");
			$Form->addForm("myForm", [
        "action" => "/register/",
        "submitlabel" => "Invia",
        "fields" => [
          ["email", "text", ["name" => "email"]]
        ]
      ]);
      $Form->setFieldTemplate("text", '<div class="myclass"><input {{ATTRIBUTES}} /></div>');
			return [
				"template" => "test.php",
				"data" => [
					"content" => "{{Form|Render|myForm}}"
				]
			];
		});
    
    $response = $machine->run(true);
    $this->assertContains('<div class="myclass"><input name="email" /></div>', $response["body"]);
  }
}
