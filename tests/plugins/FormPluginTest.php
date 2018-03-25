<?php

namespace WebEngine\Tests;

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
			"templates_path" => "tests/engine/templates/",
			"plugins_path" => "plugins/"
		];
	}
  
  public function testAddForm()
  {
    $req = $this->_request("GET", "/");
    $engine = new \WebEngine\WebEngine($req);
    $engine->addPlugin("Form");	
    
		$engine->addPage("/", function($engine) {
			$Form = $engine->plugin("Form");
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
    
    $response = $engine->run(true);

    $this->assertContains('<div class="formContainer form_myForm">', $response["body"]);
    
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
    $engine = new \WebEngine\WebEngine($req);
    $engine->addPlugin("Form");	
    
		$engine->addPage("/", function($engine) {
			$Form = $engine->plugin("Form");
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
    
    $response = $engine->run(true);
    $this->assertContains('<input id="myFormemail" type="text" value="test@test.it" name="email" />', $response["body"]);
    $this->assertContains('<input id="myFormactive" type="checkbox" name="active" checked="checked" />', $response["body"]);
  }
  
  public function testSetFieldTemplate()
  {
    $req = $this->_request("GET", "/");
    $engine = new \WebEngine\WebEngine($req);
    $engine->addPlugin("Form");	
    
		$engine->addPage("/", function($engine) {
			$Form = $engine->plugin("Form");
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
    
    $response = $engine->run(true);
    $this->assertContains('<div class="myclass"><input name="email" /></div>', $response["body"]);
  }
  
  public function testAdditionalMacros()
  {
    $req = $this->_request("GET", "/");
    $engine = new \WebEngine\WebEngine($req);
    $engine->addPlugin("Form");	
    
		$engine->addPage("/", function($engine) {
			$Form = $engine->plugin("Form");
			$Form->addForm("myForm", [
        "action" => "/register/",
        "submitlabel" => "Invia",
        "fields" => [
          ["email", "text", ["name" => "email"], ["MY_CLASS" => "theClass"]]
        ],
        
      ]);
      $Form->setFieldTemplate("text", '<div class="{{MY_CLASS}}"><input {{ATTRIBUTES}} /></div>');
			return [
				"template" => "test.php",
				"data" => [
					"content" => "{{Form|Render|myForm}}"
				]
			];
		});
    
    $response = $engine->run(true);
    $this->assertContains('<div class="theClass"><input name="email" /></div>', $response["body"]);

  }
  
  public function testSingleFields() {
    $req = $this->_request("GET", "/");
    $engine = new \WebEngine\WebEngine($req);
    $Form = $engine->addPlugin("Form");	
    
    $input = $Form->input("fieldname", "This is the field value");
    $this->assertEquals('<input id="fieldname" type="text" value="This is the field value" name="fieldname" />', $input);
  
    $input = $Form->image("fieldname", "currentvalue");
    $this->assertEquals('<input id="fieldname" type="file" data-value="currentvalue" name="fieldname" />', $input);

    // simple select
    $input = $Form->select("fieldname", [[1, "babbo"],["baabs", "baabs"]], "baabs");
    $this->assertEquals('<select id="fieldname" name="fieldname"><option value="1">babbo</option><option selected value="baabs">baabs</option></select>', $input);

    // multi select with 1 selected
    $input = $Form->select("fieldname", [[1, "babbo"],["baabs", "baabs"]], ["baabs"], true);
    $this->assertEquals('<select id="fieldname" name="fieldname" multiple="multiple"><option value="1">babbo</option><option selected value="baabs">baabs</option></select>', $input);

    // multi select with 2 selected
    $input = $Form->select("fieldname", [[1, "babbo"],["baabs", "baabs"]], ["baabs", 1], true);
    $this->assertEquals('<select id="fieldname" name="fieldname" multiple="multiple"><option selected value="1">babbo</option><option selected value="baabs">baabs</option></select>', $input);
    
    // checkbox (unchecked)
    $input = $Form->checkbox("fieldname", "nome");
    $this->assertEquals('<label><input id="fieldname" type="checkbox" name="fieldname" value="1" /> nome</label>', $input);
  
    // checkbox (checked)
    $input = $Form->checkbox("fieldname", "nome", 1, 1);
    $this->assertEquals('<label><input id="fieldname" type="checkbox" name="fieldname" value="1" checked="checked" /> nome</label>', $input);
  }
}
