<?php

namespace WebEngine\Tests;

require './vendor/autoload.php';

class WebEngineTest extends \PHPUnit_Framework_TestCase {
	private function _setOpts($method, $path)	{
		return [
      "request" => [
        "SERVER" => [
          "REQUEST_METHOD" => $method,
          "REQUEST_URI" => $path,
          "HTTP_HOST" => "localhost:8000",
          "DOCUMENT_ROOT" => "C:\www\example.com\httpdocs",
          "SCRIPT_FILENAME" => "C:\www\example.com\httpdocs/index.php"
        ]
      ],
			"templatesDir" => "tests/engine/templates/",
			"pluginsDir" => "tests/engine/plugins/",
      "controllersDir" => "tests/engine/controllers/"
		];
	}
  
	private function _setOptsInSubdir($method, $path)	{
		return [
      "request" => [
        "SERVER" => [
          "REQUEST_METHOD" => $method,
          "REQUEST_URI" => $path,
          "HTTP_HOST" => "localhost:8000",
          "DOCUMENT_ROOT" => "C:\www\example.com\httpdocs",
          "SCRIPT_FILENAME" => "C:\www\example.com\httpdocs\web/index.php"
        ]
      ],
			"templatesDir" => "tests/engine/templates/",
			"pluginsDir" => "tests/engine/plugins/",
      "controllersDir" => "tests/engine/controllers/"
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
		$response = $engine->run();
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
		$response = $engine->run();
		$this->assertEquals("<h1>Home page</h1>", $response["body"]);
	}	
  
  public function testPageOkInSubdir() {
		$engine = new \WebEngine\WebEngine($this->_setOptsInSubdir("GET", "/"));
		$engine->addPage("/", function() {
			return [
				"template" => "test.php",
				"data" => [
					"content" => "{{templateUrl}}"
				]
			];
		});
		$response = $engine->run();
		$this->assertEquals("<h1>//localhost:8000/web/tests/engine/templates/default</h1>", $response["body"]);
    $this->assertEquals("/", $engine->currentRoute);
	}
	
  public function testLoadExternalSource() {
    $engine = new \WebEngine\WebEngine($this->_setOpts("GET", "/"));
    // the external controllers are in the controllersDir (default: controllers/)
    $engine->addPage("/", 'testController');
    $response = $engine->run();
    $this->assertEquals("<h1>External controller!</h1>", $response["body"]);
  }
  
  public function testRequestWithQueryString() {
    $engine = new \WebEngine\WebEngine($this->_setOpts("GET", "/?test=1"));
		$engine->addPage("/", function() {
			return [
				"template" => "test.php",
				"data" => [
					"content" => "Home page"
				]
			];
		});
		$response = $engine->run();
		$this->assertEquals("<h1>Home page</h1>", $response["body"]);    
  }
  
	public function testRouteParams() {
		$engine = new \WebEngine\WebEngine($this->_setOpts("GET", "/languages/php/5/"));
		$engine->addPage("/languages/{language}/{version}/", function($engine, $language, $version) {
			$this->assertEquals("WebEngine\WebEngine", get_class($engine));
			$this->assertEquals("php", $language);
			$this->assertEquals("5", $version);
		});
		$response = $engine->run();		
	}

	public function testMatchSimilarRoutes() {
		$engine = new \WebEngine\WebEngine($this->_setOpts("GET", "/languages/php/6/"));
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
		$result = $engine->run();
		$this->assertEquals("<h1>right page</h1>", $result["body"]);
	}

	public function testActionOk() {
		$engine = new \WebEngine\WebEngine($this->_setOpts("POST", "/actionpost/"));
		$engine->addAction("/actionpost/", "POST", function($engine) {
			// action code
			$engine->redirect("/landing/");
		});
		$response = $engine->run();
		$headers = $response["headers"];
		$this->assertEquals(1, count($response["headers"]));
		$this->assertEquals("location: /landing/", $response["headers"][0]);
	}
	
	public function testMethodNotFoundOk() {
		$engine = new \WebEngine\WebEngine($this->_setOpts("POST", "/actionpost/"));
		$engine->addAction("/actionpost/", "GET", function($engine) {
			// action code
		});
		$response = $engine->run();
		$this->assertEquals(404, $response["code"]);
		$this->assertEquals("Not found", $response["reason"]);
	}
	
	public function testRouteNotFound()	{
    $engine = new \WebEngine\WebEngine($this->_setOpts("GET", "/non-existent-page/"));
		$engine->addPage("/", function() {
			return [
				"template" => "test.php",
				"data" => [
					"content" => "Home page"
				]
			];
		});
		$response = $engine->run();
		$this->assertEquals(404, $response["code"]);
		$this->assertEquals("Not found", $response["reason"]);
	}
	
	public function testTemplateNotFound() {	
		$engine = new \WebEngine\WebEngine($this->_setOpts("GET", "/"));
		$engine->addPage("/", function() {
			return [
				"template" => "non-existent-template.php",
				"data" => [
					"content" => "Home page"
				]
			];
		});
		$response = $engine->run();
		$this->assertEquals("Missing template file: "
			. "tests/engine/templates/default/non-existent-template.php", $response["body"]);
	}
	
	public function testRouteDuplicated()	{
		$engine = new \WebEngine\WebEngine($this->_setOpts("GET", "/"));
		$engine->addPage("/", function() {
			//
		});
		
		$result = $engine->addPage("/duplicated/", function() {
			//
		});
		$this->assertEquals("object", gettype($result));
		
		$result = $engine->addPage("/duplicated/", function() {
			//
		});
		$this->assertEquals("Config Error: duplicated route. Route exists "
			. "for GET method (/duplicated/)", $result);
	}
	
	public function testAddDefaultPlugin() {
		$engine = new \WebEngine\WebEngine($this->_setOpts("GET", "/"));
		$engine->addPlugin("Link");
		$this->assertEquals("WebEngine\Plugin\Link", get_class($engine->plugin("Link")));
	}
	
	public function testAddUserPlugin() {
		$engine = new \WebEngine\WebEngine($this->_setOpts("GET", "/"));
		$engine->addPlugin("Sample");
		$this->assertEquals("WebEngine\Plugin\Sample", get_class($engine->plugin("Sample")));
	}
	
	public function testAddThirdpartyPlugin() {
		include("Thirdparty.php");
		$engine = new \WebEngine\WebEngine($this->_setOpts("GET", "/"));
		$engine->addPlugin("Thirdparty");
		$this->assertEquals("WebEngine\Plugin\Thirdparty", get_class($engine->plugin("Thirdparty")));
	}

	public function testAddNonExistentPlugin() {
		$engine = new \WebEngine\WebEngine($this->_setOpts("GET", "/"));
		$result = $engine->addPlugin("NonExistent");
		$this->assertEquals(NULL, $result);
	}

	public function testUsePlugin() {
		$engine = new \WebEngine\WebEngine($this->_setOpts("GET", "/"));
		$engine->addPlugin("Sample");
		$engine->addPage("/", function() {
			return [
				"template" => "testplug.php",
				"data" => [
					"content" => "{{Sample|Plugfun|par1|par2|par3}}"
				]
			];
		});
		$response = $engine->run();
		
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

	public function testTemplateTag() {
		$engine = new \WebEngine\WebEngine($this->_setOpts("GET", "/"));
		$engine->addPage("/", function() {
			return [
				"template" => "test.php",
				"data" => [
					"content" => "{{templateUrl}}"
				]
			];
		});
		$response = $engine->run();
		$this->assertEquals("<h1>//localhost:8000/tests/engine/templates/default</h1>", $response["body"]);
	}
	
	public function testSetCookie()	{
		$engine = new \WebEngine\WebEngine($this->_setOpts("POST", "/login/"));
		$engine->addAction("/login/", "POST", function($engine) {
			$engine->setCookie("loggedIn", 1);
		});
		$response = $engine->run();
		$this->assertEquals(1, $response["cookies"][0][1]);		
	}
		
	public function testSendError()	{
		$engine = new \WebEngine\WebEngine($this->_setOpts("POST", "/myaction/"));
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
		$response = $engine->run();
		$this->assertEquals(303, $response["code"]);
	}
	
	public function testActionApi()	{
		$engine = new \WebEngine\WebEngine($this->_setOpts("POST", "/api/tables/"));
		$engine->addAction("/api/tables/", "POST", function($engine) {
			$engine->setResponseCode(200);
			$body = json_encode(["table1", "table2"]);
			$engine->setResponseBody($body);
		});
		$response = $engine->run();
		$this->assertEquals(200, $response["code"]);
		$this->assertEquals('["table1","table2"]', $response["body"]);
	}
	
	public function testSameRouteMatch() {
		$engine = new \WebEngine\WebEngine($this->_setOpts("POST", "/api2/tables/"));
		// definition order is important! Before, the most static.
		$engine->addAction("/api2/tables/", "POST", function($engine) {
			$engine->setResponseCode(200);
			$engine->setResponseBody("fixed");
		});
		$engine->addAction("/{tablename}/{id}/", "POST", function($engine) {
			$engine->setResponseCode(200);
			$engine->setResponseBody("wildcards");
		});
		$response = $engine->run();
		$this->assertEquals("fixed", $response["body"]);
	}

	public function testServe() {
		$engine = new \WebEngine\WebEngine($this->_setOpts("GET", "/assets/js/lib1/lib1.js"));
		$engine->addAction("/assets/{filename:.+}", "GET", function($engine, $filename) {
			$serverpath = __DIR__ . "/plugins/Sample/template/" . $filename;
			$engine->serve($serverpath);
		});
		$response = $engine->run();
		$this->assertEquals(200, $response["code"]);
		$this->assertEquals("console.log('lib1.js');", $response["body"]);
	}
  
  public function testMiddleware() {
    $engine = new \WebEngine\WebEngine($this->_setOpts("GET", "/"));
    $engine->addPage("/", function() {
      return [
				"template" => "test.php",
				"data" => [
					"content" => "home page"
				]
      ];
    })->mw(function($req, $resp, $next) {
      return $resp;
    });
  }
}
