<?php

namespace WebEngine\Tests;

require './vendor/autoload.php';

class WebEngineTest extends \PHPUnit_Framework_TestCase {
	private function _setOpts($method, $path)	{
		return [
			"SERVER" => [
				"REQUEST_METHOD" => $method,
				"REQUEST_URI" => $path,
				"HTTP_HOST" => "localhost:8000",
        "DOCUMENT_ROOT" => "C:\www\example.com\httpdocs",
        "SCRIPT_FILENAME" => "C:\www\example.com\httpdocs/index.php"
			],
			"templates_path" => "tests/engine/templates/",
			"plugins_path" => "tests/engine/plugins/"
		];
	}
  
	private function _setOptsInSubdir($method, $path)	{
		return [
			"SERVER" => [
				"REQUEST_METHOD" => $method,
				"REQUEST_URI" => $path,
				"HTTP_HOST" => "localhost:8000",
        "DOCUMENT_ROOT" => "C:\www\example.com\httpdocs",
        "SCRIPT_FILENAME" => "C:\www\example.com\httpdocs\web/index.php"
			],
			"templates_path" => "tests/engine/templates/",
			"plugins_path" => "tests/engine/plugins/"
		];
	}
	
	public function testPageOk() {
		$engine = new \WebEngine\WebEngine($this->_setOpts("GET", "/"));
		$engine->addPage("/", function() {
			return [
				"template" => "test.php",
				"data" => [
					"content" => "Home page"
				]
			];
		});
		$response = $engine->run(true);
		$this->assertEquals("<h1>Home page</h1>", $response["body"]);
	}	
  
	public function testPageOkTemplateCode() {
		$engine = new \WebEngine\WebEngine($this->_setOpts("GET", "/"));
		$engine->addPage("/", function() {
			return [
				"templateCode" => "<h1>{{content}}</h1>",
				"data" => [
					"content" => "Home page"
				]
			];
		});
		$response = $engine->run(true);
		$this->assertEquals("<h1>Home page</h1>", $response["body"]);
	}	
  
  public function testPageOkInSubdir() {
		$engine = new \WebEngine\WebEngine($this->_setOpts("GET", "/web/"));
		$engine->addPage("/", function() {
			return [
				"template" => "test.php",
				"data" => [
					"content" => "{{templatePath}}"
				]
			];
		});
		$response = $engine->run(true);
		$this->assertEquals("<h1>//localhost:8000/web/tests/engine/templates/default/</h1>", $response["body"]);
    $this->assertEquals("/", $engine->getCurrentPath());
	}
	
  public function testLoadExternalSource() {
    $engine = new \WebEngine\WebEngine($this->_setOpts("GET", "/"));
    // the external controllers are in the controllersDir (default: controllers/)
    $engine->addPage("/", 'filename::methodname');
    $response = $engine->run(true);
    $this->assertEquals("<h1>External controller!</h1>", $response["body"]);
    $this->assertEquals("/", $engine->getCurrentPath());
  }
  
  // ---
  public function testRequestWithQueryString()
  {
		$req = $this->_request("GET", "/?test=1");
		
		$engine = new \WebEngine\WebEngine($req);
		$engine->addPage("/", function() {
			return [
				"template" => "test.php",
				"data" => [
					"content" => "Home page"
				]
			];
		});
		$response = $engine->run(true);
		$this->assertEquals("<h1>Home page</h1>", $response["body"]);    
    $this->assertEquals("/", $engine->getCurrentPath());
  }
  
	public function testSetTemplate()
	{
		$req = $this->_request("GET", "/");
		
		$engine = new \WebEngine\WebEngine($req);
		$engine->setTemplate("testtemplate");
		$engine->addPage("/", function() {
			return [
				"template" => "test.php",
				"data" => [
					"content" => "Home page"
				]
			];
		});
		$response = $engine->run(true);
		$this->assertEquals("<h1>TEST TEMPLATE Home page</h1>", $response["body"]);
	}
	
	public function testRouteParams()
	{
		$req = $this->_request("GET", "/languages/php/5/");
		
		$engine = new \WebEngine\WebEngine($req);
		$engine->addPage("/languages/{language}/{version}/", function($engine, $language, $version) {
			$this->assertEquals("WebEngine\WebEngine", get_class($engine));
			$this->assertEquals("php", $language);
			$this->assertEquals("5", $version);
		});
		$response = $engine->run(true);		
	}
	
	public function testMatchSimilarRoutes()
	{
		$req = $this->_request("GET", "/languages/php/6/");
		
		$engine = new \WebEngine\WebEngine($req);
		$engine->addPage("/languages/{language}/", function($engine) {
			return [
				"template" => "test.php",
				"data" => [
					"content" => "wrong page"
				]
			];
		});
		$engine->addPage("/languages/{language}/{id}/", function($engine) {
			return [
				"template" => "test.php",
				"data" => [
					"content" => "right page"
				]
			];
		});
		$result = $engine->run(true);
		$this->assertEquals("<h1>right page</h1>", $result["body"]);
	}
	
	public function testActionOk()
	{
		$req = $this->_request("POST", "/actionpost/");
		
		$engine = new \WebEngine\WebEngine($req);
		$engine->addAction("/actionpost/", "POST", function($engine) {
			// action code
			$engine->redirect("/landing/");
		});
		$response = $engine->run(true);
		$headers = $response["headers"];
		$this->assertEquals(1, count($response["headers"]));
		$this->assertEquals("location: /landing/", $response["headers"][0]);
	}
	
	public function testMethodNotFoundOk()
	{
		$req = $this->_request("POST", "/actionpost/");
		
		$engine = new \WebEngine\WebEngine($req);
		$engine->addAction("/actionpost/", "GET", function($engine) {
			// action code
		});
		$response = $engine->run(true);
		$this->assertEquals(404, $response["code"]);
		$this->assertEquals("Not found", $response["reason"]);
	}
	
	public function testRouteNotFound()
	{
		$req = $this->_request("GET", "/non-existent-page/");
		
		$engine = new \WebEngine\WebEngine($req);
		$engine->addPage("/", function() {
			return [
				"template" => "test.php",
				"data" => [
					"content" => "Home page"
				]
			];
		});
		$response = $engine->run(true);
		$this->assertEquals(404, $response["code"]);
		$this->assertEquals("Not found", $response["reason"]);
	}
	
	public function testTemplateNotFound()
	{
		$req = $this->_request("GET", "/");
		
		$engine = new \WebEngine\WebEngine($req);
		$engine->addPage("/", function() {
			return [
				"template" => "non-existent-template.php",
				"data" => [
					"content" => "Home page"
				]
			];
		});
		$response = $engine->run(true);
		$this->assertEquals("Missing template file: "
			. "tests/engine/templates/default/non-existent-template.php", $response["body"]);
	}
	
	public function testRouteDuplicated()
	{
		$req = $this->_request("GET", "/");
		
		$engine = new \WebEngine\WebEngine($req);
		$engine->addPage("/", function() {
			//
		});
		
		$result = $engine->addPage("/duplicated/", function() {
			//
		});
		$this->assertEquals("", $result);
		
		$result = $engine->addPage("/duplicated/", function() {
			//
		});
		$this->assertEquals("Config Error: duplicated route. Route exists "
			. "for GET method (/duplicated/)", $result);
	}
	
	public function testAddDefaultPlugin() 
	{
		$req = $this->_request("GET", "/");
		
		$engine = new \WebEngine\WebEngine($req);
		$engine->addPlugin("Link");
		$this->assertEquals("WebEngine\Plugin\Link", get_class($engine->plugin("Link")));
	}
	
	public function testAddUserPlugin() 
	{
		$req = $this->_request("GET", "/");
		
		$engine = new \WebEngine\WebEngine($req);
		$engine->addPlugin("Sample");
		$this->assertEquals("WebEngine\Plugin\Sample", get_class($engine->plugin("Sample")));
	}
	
	public function testAddThirdpartyPlugin() 
	{
		include("Thirdparty.php");
		
		$req = $this->_request("GET", "/");
		
		$engine = new \WebEngine\WebEngine($req);
		$engine->addPlugin("Thirdparty");
		$this->assertEquals("WebEngine\Plugin\Thirdparty", get_class($engine->plugin("Thirdparty")));
	}
	
	public function testAddNonExistentPlugin() 
	{
		$req = $this->_request("GET", "/");
		
		$engine = new \WebEngine\WebEngine($req);
		$result = $engine->addPlugin("NonExistent");
		$this->assertEquals(NULL, $result);
	}
	
	public function testUsePlugin() 
	{
		$req = $this->_request("GET", "/");
		
		$engine = new \WebEngine\WebEngine($req);
		$engine->addPlugin("Sample");
		$engine->addPage("/", function() {
			return [
				"template" => "testplug.php",
				"data" => [
					"content" => "{{Sample|Plugfun|par1|par2|par3}}"
				]
			];
		});
		$response = $engine->run(true);
		
		// {{Sample|plugfun|par1|par2|par3}}
		$this->assertContains(
			"<p>Sample plugin function called with params par1, par2, par3</p>", 
			$response["body"]
		);
		// echo $Sample->plugFun("test1");
		$this->assertContains(
			"<p>Sample plugin function called with params test1</p>", 
			$response["body"]
		);
		// echo $Sample->plugFun(["test2"]);
		$this->assertContains(
			"<p>Sample plugin function called with params test2</p>", 
			$response["body"]
		);
		// echo $Sample->plugFun(["par4", "par5"]);
		$this->assertContains(
			"<p>Sample plugin function called with params par4, par5</p>", 
			$response["body"]
		);

		$result = $engine->plugin("Sample")->Plugfun("test");
		$this->assertEquals("Sample plugin function called with params test", $result);
	}
	
	public function testTemplateTag()
	{
		$req = $this->_request("GET", "/");
		
		$engine = new \WebEngine\WebEngine($req);
		$engine->addPage("/", function() {
			return [
				"template" => "test.php",
				"data" => [
					"content" => "{{templatePath}}"
				]
			];
		});
		$response = $engine->run(true);
		$this->assertEquals("<h1>//localhost:8000/tests/engine/templates/default/</h1>", $response["body"]);
	}
	
	public function testGetRequest()
	{
		$req = $this->_request("GET", "/");
		
		$engine = new \WebEngine\WebEngine($req);
		$r = $engine->getRequest();
		
		$this->assertEquals("GET", $r["SERVER"]["REQUEST_METHOD"]);
		$this->assertEquals("/", $r["SERVER"]["REQUEST_URI"]);
		$this->assertEquals("localhost:8000", $r["SERVER"]["HTTP_HOST"]);
	}
	
	public function testSetCookie()
	{
		$req = $this->_request("POST", "/login/");
		$engine = new \WebEngine\WebEngine($req);
		$engine->addAction("/login/", "POST", function($engine) {
			$engine->setCookie("loggedIn", 1);
		});
		$response = $engine->run(true);
		$this->assertEquals(1, $response["cookies"][0][1]);		
	}
	
	public function testExecuteHook()
	{
		$req = $this->_request("POST", "/plugfun/");
		$engine = new \WebEngine\WebEngine($req);
		$sample = $engine->addPlugin("Sample");
		$sample->addHook("after_plugfun", function($engine, $param1, $param2) {
			$engine->redirect("/landing/" . $param1 . "/" . $param2 . "/");
		});
		$engine->addAction("/plugfun/", "POST", function($engine) {
			$engine->plugin("Sample")->Plugfun(["john", "jane"]);
		});
		$response = $engine->run(true);
		
		$this->assertEquals("location: /landing/john/jane/", $response["headers"][0]);
	}
	
	public function testSendError()
	{
		$req = $this->_request("POST", "/myaction/");
		$engine = new \WebEngine\WebEngine($req);
		$engine->addAction("/myaction/", "POST", function($engine) {
			$engine->setResponseCode(303);
			$engine->redirect("/");
		});
		$engine->addPage("/", function() {
			return [
				"template" => "test.php",
				"data" => [
					"content" => "home page"
				]
			];
		});
		$response = $engine->run(true);
		$this->assertEquals(303, $response["code"]);
	}
	
	public function testActionApi()
	{
		$req = $this->_request("POST", "/api/tables/");
		$engine = new \WebEngine\WebEngine($req);
		$engine->addAction("/api/tables/", "POST", function($engine) {
			$engine->setResponseCode(200);
			$body = json_encode(["table1", "table2"]);
			$engine->setResponseBody($body);
		});
		$response = $engine->run(true);
		$this->assertEquals(200, $response["code"]);
		$this->assertEquals('["table1","table2"]', $response["body"]);
	}
	
	public function testSameRouteMatch()
	{
		$req = $this->_request("POST", "/api2/tables/");
		$engine = new \WebEngine\WebEngine($req);
		// definition order is important! Before, the most static.
		$engine->addAction("/api2/tables/", "POST", function($engine) {
			$engine->setResponseCode(200);
			$engine->setResponseBody("fixed");
		});
		$engine->addAction("/{tablename}/{id}/", "POST", function($engine) {
			$engine->setResponseCode(200);
			$engine->setResponseBody("wildcards");
		});
		$response = $engine->run(true);
		$this->assertEquals("fixed", $response["body"]);
	}
	
	public function testPluginRoutes()
	{
		$req = $this->_request("GET", "/plug/");
		$engine = new \WebEngine\WebEngine($req);
		$sample = $engine->addPlugin("Sample");
		$sample->setRoutes("/plug");
		$response = $engine->run(true);
		$this->assertEquals("TEST<span>Home page</span>", $response["body"]);
	}
	
	public function testServe()
	{
		$req = $this->_request("GET", "/assets/js/lib1/lib1.js");
		$engine = new \WebEngine\WebEngine($req);
		$engine->addAction("/assets/{filename:.+}", "GET", function($engine, $filename) {
			$serverpath = __DIR__ . "/plugins/Sample/template/" . $filename;
			$engine->serve($serverpath);
		});
		$response = $engine->run(true);
		$this->assertEquals(200, $response["code"]);
		$this->assertEquals("console.log('lib1.js');", $response["body"]);
	}
}
