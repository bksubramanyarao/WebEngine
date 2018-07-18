<?php
/**
 * WebEngine
 *
 * PHP version 7
 *
 * @category  Core
 * @package   WebEngine
 * @author    Paolo Savoldi <paooolino@gmail.com>
 * @copyright 2017-2018 Paolo Savoldi
 * @license   https://github.com/paooolino/WebEngine/blob/master/LICENSE 
 *            (Apache License 2.0)
 * @link      https://github.com/paooolino/WebEngine
 */
namespace WebEngine;

/**
 * WebEngine
 *
 * Create a web application instantiating this class.
 *
 * @category Core
 * @package  WebEngine
 * @author   Paolo Savoldi <paooolino@gmail.com>
 * @license  https://github.com/paooolino/WebEngine/blob/master/LICENSE 
 *           (Apache License 2.0)
 * @link     https://github.com/paooolino/WebEngine
 */
class WebEngine
{
  public $_SERVER;
  public $_GET;
  public $_POST;
  public $_COOKIE;
  public $_FILES;
  
  /*
   *  Difference between SCRIPT_FILENAME and DOCUMENT_ROOT
   *  e.g.: /index.php, /subdir/index.php
   */
  public $scriptPath;
  
  /*
   *  Framework subdir.
   *  e.g.: "", "subdir", "subdir1/subdir2"
   */
  public $baseDir;
  
  /*
   *  Template Url.
   *
   */
  public $templateUrl;
  
  /*
   *  Difference between REQUEST_URI and QUERY_STRING.
   *  e.g.: "/"
   */
  public $currentRoute;
  
  public $templatesDir;
  public $pluginsDir;
  public $controllersDir;
  public $templateName;
  
  private $_routes;
  private $_plugins;
  private $_response; 
  private $_debug_infos;
  
  public $append_debug_infos;

  private $_allowedCTypes = [
    "css" => "text/css",
    "js" => "application/javascript",
    "json" => "application/json",
    "jpg" => "image/jpeg",
    "jpeg" => "image/jpeg",
    "gif" => "image/gif",
    "mid" => "audio/midi",
    "midi" => "audio/midi",
    "png" => "image/png",
    "svg" => "image/svg+xml",
    "pdf" => "application/pdf",
    "mp4" => "video/mp4"
  ];
    
  /**
   * Create new engine.
   *
   * Available options are:
   * - request
   *  - SERVER
   *  - GET
   *  - POST
   *  - COOKIE
   *  - FILES
   * - templatesDir
   * - pluginsDir
   *
   * @param array $opts an array of options.
   */
  public function __construct($opts=[]) {
    $this->_SERVER        = $opts["request"]["SERVER"]  ?? $_SERVER;
    $this->_GET           = $opts["request"]["GET"]     ?? $_GET;
    $this->_POST          = $opts["request"]["POST"]    ?? $_POST;
    $this->_COOKIE        = $opts["request"]["COOKIE"]  ?? $_COOKIE;
    $this->_FILES         = $opts["request"]["FILES"]   ?? $_FILES;
    $this->templatesDir   = $opts["templatesDir"]       ?? "templates/";
    $this->pluginsDir     = $opts["pluginsDir"]         ?? "plugins/";
    $this->controllersDir = $opts["controllersDir"]     ?? "controllers/";
    $this->templateName   = $opts["templateName"]       ?? "default";

    $this->scriptPath     = str_replace(
      $this->_SERVER["DOCUMENT_ROOT"], "",      
      $this->_SERVER["SCRIPT_FILENAME"]
    );
    $this->baseDir        = ltrim(dirname($this->scriptPath), DIRECTORY_SEPARATOR);
    $this->baseDir = $this->baseDir == "" ? "" : $this->baseDir . "/";
    $this->templateUrl    = "//" . $this->_SERVER["HTTP_HOST"] . "/" . 
      $this->baseDir . $this->templatesDir . $this->templateName;
    $this->currentRoute   = explode("?", $this->_SERVER["REQUEST_URI"])[0];
    
    $this->_routes = [];
    $this->_plugins = [];
    $this->_debug_infos = [];
    $this->append_debug_infos = false;
  
    $this->_response = [
      "headers" => [],
      "code" => "",
      "reason" => "",
      "body" => "",
      "cookies" => []
    ];
  }
  
  /**
   * Add a route without side effects.
   *
   * @param string   $name the route name.
   * @param function $cb   the callback function to be executed.
   *
   * @return string Error
   */
  public function addPage($name, $cb)
  {
    return $this->_addRoute($name, "GET", $cb);
  }
  
  /**
   * Add a route with side effects.
   *
   * @param string   $name   the route name.
   * @param string   $method the method name; either "POST" or "GET".
   * @param function $cb     the callback function to be executed.
   *
   * @return string Error
   */
  public function addAction($name, $method, $cb)
  {
    return $this->_addRoute($name, $method, $cb);
  }
  
  /**
   * Add a plugin by name.
   *
   * @param string $name the plugin name.
   *
   * @return Plugin The plugin itself
   */    
  public function addPlugin($name)
  {
    $className = "\\WebEngine\\Plugin\\" . $name;
    
    // look in the project plugins folder
    if (!class_exists($className)) {
      $project_plugin_path = $this->pluginsDir . $name . "/" . $name . ".php"; 
      if (file_exists($project_plugin_path)) {
        include $project_plugin_path;
      }
    }
    
    // look in the default WebEngine plugins folder
    if (!class_exists($className)) {
      $default_plugin_path = __DIR__ . "/../plugins/" . $name . "/" . $name . ".php"; 
      if (file_exists($default_plugin_path)) {
        include $default_plugin_path;
      }
    }
        
    // ready to instantiate
    if (class_exists($className)) {
      $this->_plugins[$name] = new $className($this);
      return $this->_plugins[$name];
    }
  }
  
  /**
   * Executes an hook.
   *
   * This is intended to be executed by plugins.
   *
   * @return void
   */
  public function executeHook($arrFunc, $arguments) 
  {
    foreach ($arrFunc as $func) {
      call_user_func_array($func, array_merge([$this], $arguments));
    }
  }
    
  /**
   * Return the plugin instance.
   *
   * @param string $name the plugin name.
   *
   * @return PluginInstance The plugin instance 
   */
  public function plugin($name) 
  {
    return isset($this->_plugins[$name]) ? $this->_plugins[$name] : null;
  }
    
  /**
   * Redirect toward a specified route name.
   *
   * @param string $path the route name.
   *
   * @return void
   */
  public function redirect($path)
  {
    if ($this->_response["code"] == "") {
      $this->_response["code"] = 302;
      $this->_response["headers"][] = "location: " . $path;
    }
  }
    
  /**
   * PHP setCookie wrapper
   *
   * @return void
   */
  public function setCookie()
  {
    $this->_response["cookies"][] = func_get_args();
  }

  /**
   * Send error header
   *
   * @param string $errnum The HTTP error number
   *
   * @return void
   */    
  public function setResponseCode($errnum)
  {
    if ($this->_response["code"] == "") {
      $this->_response["code"] = $errnum;
    }
  }
    
  /**
   * Set response body
   *
   * @param string $body The response body
   *
   * @return void
   */    
  public function setResponseBody($body)
  {
    $this->_response["body"] = $body;
  }
  
  /**
   * Return the template path.
   *
   * Used to link assets in templates.
   *
   * @return string The template path.
   */
  public function templatePath()
  {
    return "//" . $this->_SERVER["HTTP_HOST"] . $this->basepath . "/" . $this->_templates_path 
      . $this->_template_name . "/";
  }
    
  /**
   * Return the request infos.
   *
   * May be used in plugins.
   *
   * @return array Containing SERVER, POST, COOKIE arrays.
   */
  public function getRequest()
  {
    return [
      "SERVER" => $this->_SERVER,
      "POST" => $this->_POST,
      "COOKIE" => $this->_COOKIE,
      "GET" => $this->_GET,
      "FILES" => $this->_FILES
    ];
  }
    
  /**
   *  Shortcut for GET variables
   */
  public function GET($k)
  {
    return isset($this->_GET[$k]) ? $this->_GET[$k] : "";
  }
  
  /**
   *  Shortcut for POST variables
   */
  public function POST($k)
  {
    return isset($this->_POST[$k]) ? $this->_POST[$k] : "";
  }
  
  /**
   *  Shortcut for cookies
   */
  public function COOKIE($k)
  {
    return isset($this->_COOKIE[$k]) ? $this->_COOKIE[$k] : "";
  }
  
  /**
   *  Shortcut for server globals
   */
  public function SERVER($k)
  {
    return isset($this->_SERVER[$k]) ? $this->_SERVER[$k] : "";
  }
  
  /**
   *  redirect to the page referrer
   */   
  public function back()
  {
    $r = $this->getRequest();
    if (isset($r["SERVER"]["HTTP_REFERER"])) {
      $referrer = $r["SERVER"]["HTTP_REFERER"];
      if ($referrer) {
        $this->redirect($referrer);
      }
    } else {
      $this->redirect("/");
    }
  }
  
  public function getCurrentPath()
  {
    // get the complete path, including possible subfolders.
    $path = $this->_SERVER["REQUEST_URI"];
    
    // strip the subfolder from path.
    if (substr($path, 0, strlen($this->basepath)) == $this->basepath) {
      $path = substr($path, strlen($this->basepath));
    } 

    // strip query string and decode uri
    if (false !== $pos = strpos($path, '?')) {
      $path = substr($path, 0, $pos);
    }
    $path = rawurldecode($path);
    
    return $path;
  }
  
  public function add_debug_info($str)
  {
    $this->_debug_infos[] = $str;
  }
  
  /**
   * Run the application.
   *
   * @return array A response array
   */
  public function run() {
    // reset the response, as the engine may be re-run
    $this->_response = [
      "headers" => [],
      "code" => "",
      "reason" => "",
      "body" => "",
      "cookies" => []
    ];
    
    // 1. Get route info
    $route_matchinfo = $this->_matchRoute($this->currentRoute, $this->_SERVER["REQUEST_METHOD"]);   
    if (!$route_matchinfo) {
      $this->_response["code"] = 404;
      $this->_response["reason"] = "Not found";
      return $this->_response;
    }

    // look for callback (closure or external)
    $cb = $route_matchinfo["callback"];
    if (is_string($cb)) {
      $external_controller = $this->controllersDir . $cb . ".php";
      if (file_exists($external_controller)) {
        include($external_controller);
        $cb = "\\WebEngine\\Controllers\\" . $cb;
      }
    }
    $reflFunc = new \ReflectionFunction($cb);
    $this->add_debug_info("route: " . $route_matchinfo["routename"]);
    $Link = $this->plugin("Link");
    if (isset($Link)) {
      $this->add_debug_info("route name: " . $this->plugin("Link")->getRouteName($route_matchinfo["routename"]));
    }
    $this->add_debug_info("route callback defined in: " . $reflFunc->getFileName() . ':' . $reflFunc->getStartLine());
    
    // 2. Execute callback
    $result = call_user_func_array(
      $cb, 
      $route_matchinfo["params"]
    );
    if ($this->_response["code"] != "") {
      return $this->_response;
    }
    
    // 3. Check for template or templateCode in result
    if (!(isset($result["data"]) && (isset($result["template"]) || isset($result["templateCode"])))) {
      // a route was found but nor a response was set or a 
      //  page was found.
      $this->_response["code"] = 404;
      $this->_response["reason"] = "Not found";   
      return $this->_response;
    }
    
    // 4. Check for templateCode
    if (isset($result["templateCode"])) {
      $this->_response["code"] = 200;
      $this->_response["reason"] = "OK";
      $this->_response["body"] = $this->populateTemplate(
        $result["templateCode"], 
        $result["data"]
      );
      return $this->_response;
    }
    
    // 5. Execute the template file
    // page found. 200 OK
    $this->_response["code"] = 200;
    $this->_response["reason"] = "OK";
    $this->_response["body"] = $this->_getOutputTemplate(
      $result["template"], 
      $result["data"]
    );
    if ($this->append_debug_infos) {
      $this->_response["body"] .= $this->_getDebugInfos();
    }
 
    /*
    if (!$silent) {
      foreach ($this->_response["cookies"] as $cookieparams) {
        call_user_func_array("setcookie", $cookieparams);
      }
      
      foreach ($this->_response["headers"] as $header) {
        header($header);
      }
      
      http_response_code($this->_response["code"]);
  
      if ($this->_response["code"] == 200) {
        echo $this->_response["body"];
      } else {
        echo $this->_response["reason"];
      }
      
      die();
    }
    */
    
    return $this->_response;
  }
  
  /**
   * Serve a static asset.
   *
   * @param string $serverpath The absolute server file path.
   *
   * @return void
   */
  public function serve($serverpath)
  {
    $this->_response["code"] = 404;
    if (file_exists($serverpath)) {
      $path_parts = pathinfo($serverpath);
      $ext = $path_parts["extension"];
      if (isset($this->_allowedCTypes[$ext])) {
        $contentType = $this->_allowedCTypes[$ext];
        $this->_response["code"] = 200;
        $this->_response["headers"][] = "Content-type: " . $contentType;
        $this->_response["body"] = file_get_contents($serverpath);
      }
    }
  }
    
  private function _getDebugInfos() {
    $html = "";
    $html .= "<!--\r\n";
    $html .= print_r($this->_debug_infos, true);
    $html .= "-->\r\n";
    return $html;
  }
  
  /**
   * Add a generic route.
   *
   * @param string   $name   the route name.
   * @param string   $method the method name; either "POST" or "GET".
   * @param function $cb     the callback function to be executed.
   *
   * @return string Error
   */
  private function _addRoute($name, $method, $cb) {
    if (isset($this->_routes[$name][$method])) {
      return "Config Error: duplicated route. Route exists for $method " 
        . "method ($name)";
    }
    if (!isset($this->_routes[$name])) {
      $this->_routes[$name] = [];
    }
    $this->_routes[$name][$method] = $cb;
    return "";
  }
  
  /**
   * Check if a route matches the passed route.
   *
   * @param string $route   the route to check.
   * @param string $method the request method; either "POST" or "GET".
   *
   * @return array|void the matched route infos (callback and params).
   */
  private function _matchRoute($route, $method) {
    $dispatcher = \FastRoute\simpleDispatcher(
      function (\FastRoute\RouteCollector $r) use ($method) {
        foreach ($this->_routes as $routename => $routearr) {
          if (isset($routearr[$method])) {
            $r->addRoute($method, $routename, $routearr[$method]);
          }
        }
      }
    );
    
    $routeInfo = $dispatcher->dispatch($method, $route);

    switch ($routeInfo[0]) {
      case \FastRoute\Dispatcher::NOT_FOUND:
        // ... 404 Not Found
        break;
      case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // ... 405 Method Not Allowed
        break;
      case \FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        return [
          "routename" => $route,
          "wildcards" => isset($vars) ? count($vars) : 0,
          "callback" => $handler,
          "params" => array_merge(
            // the WebEngine object is passed as first param
            [$this], 
            isset($vars) ? $vars : []
          )
        ];
        // ... call $handler with $vars
        break;
    }
  }
    
  /**
   * Process the template file and mixes it with data.
   *
   * @param string $tpl  the template file name
   * @param array  $data an associative array of data fields.
   *
   * @return string the resulting html output.
   */
  private function _getOutputTemplate($tpl, $data) {
    $output = "";
    
    $template_file_name = $this->templatesDir
      . $this->templateName . "/" . $tpl;
    
    if (file_exists($template_file_name)) {
      $this->add_debug_info("Template: " . $template_file_name);
      // plugins are available under their name
      //  this lets to write in templates
      //    $Auth->logged_user_id
      //  instead of
      //    $this->plugin("Auth")->logged_user_id
      foreach ($this->_plugins as $name => $instance) {
          $$name = $instance;
      }
      
      // data fields are available as regular php variables in templates
      foreach ($data as $k => $v) {
          $$k = $v;
      }
      
      ob_start();
      include $template_file_name;
      $template = ob_get_contents();
      ob_end_clean();
      
      $output = $this->populateTemplate($template, $data);
    } else {
      $output = "Missing template file: " . $template_file_name;
    }
    
    return $output;
  }
    
  /**
   * Mixes a plain html template with data
   *
   * This is public in order to be used by plugins
   *
   * @param string $tpl  the template file name
   * @param array  $data an associative array of data fields.
   *
   * @return string the resulting html output.
   */
  public function populateTemplate($tpl, $data)
  {
    // populate simple tag with data
    foreach ($data as $k => $v) {
      // if a string, try the tag substitution
      if (gettype($v) == "string" || gettype($v) == "integer") {
        $tpl = str_replace("{{".$k."}}", $v, $tpl);
      }
    }
    
    // make available some engine functions intended to be used in templates
    // e.g. use {{templatePath}} instead of
    // echo $this->templatePath();
    $tags = [];
    preg_match_all("/{{(.*?)}}/", $tpl, $tags);
    for ($i = 0; $i < count($tags[0]); $i++) {
      $parts = explode("|", $tags[1][$i]);
      $method = array_shift($parts);
      if (isset($this->{$method})) {
        $value = $this->{$method};
        $tpl = str_replace($tags[0][$i], $value, $tpl);
      }
      if (method_exists($this, $method)) {
        $value = $this->{$method}($parts);
        $tpl = str_replace($tags[0][$i], $value, $tpl);
      }
    }
    
    // find plugin tags
    //  eg {{<plugin_name>|<param1>|<param2>}}
    $tags = [];
    preg_match_all("/{{(.*?)\|(.*?)}}/", $tpl, $tags);
    for ($i = 0; $i < count($tags[0]); $i++) {
      $pluginName = $tags[1][$i];
      if (isset($this->_plugins[$pluginName])) {
        $parts = explode("|", $tags[2][$i]);
        $pluginMethod = array_shift($parts);
        if (method_exists($this->_plugins[$pluginName], $pluginMethod)) {
          $value = $this->_plugins[$pluginName]->{$pluginMethod}($parts);
          $tpl = str_replace($tags[0][$i], $value, $tpl);
        } else {
          //die("Tag plugin not managed " . $pluginName . "->" 
          //  . $pluginMethod);
        }
      } else {
        //die("Plugin not managed " . $pluginName);
      }
    }
    
    return $tpl;
  }
}
