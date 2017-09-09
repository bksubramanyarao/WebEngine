<?php
/**
 * The Machine
 *
 * PHP version 5
 *
 * @category  Core
 * @package   Machine
 * @author    Paolo Savoldi <paooolino@gmail.com>
 * @copyright 2017 Paolo Savoldi
 * @license   https://github.com/paooolino/Machine/blob/master/LICENSE 
 *            (Apache License 2.0)
 * @link      https://github.com/paooolino/Machine
 */
namespace Machine;

/**
 * Machine
 *
 * Create a web application instantiating this class.
 *
 * @category Core
 * @package  Machine
 * @author   Paolo Savoldi <paooolino@gmail.com>
 * @license  https://github.com/paooolino/Machine/blob/master/LICENSE 
 *           (Apache License 2.0)
 * @link     https://github.com/paooolino/Machine
 */
class Machine
{
    private $_SERVER;
    private $_GET;
    private $_POST;
    private $_COOKIE;
    private $_FILES;
  
    private $_routes;
    private $_plugins;
  
    private $_templates_path;
    private $_plugins_path;
    
    private $_template_name;
    
    private $_response;
    
    /**
     * Create new machine.
     *
     * Available options are:
     * - SERVER
     * - POST
     * - COOKIE
     * - templates_path
     * - plugins_path
     *
     * @param array $opts an array of options.
     */
    public function __construct($opts=[])
    {
        $this->_SERVER = isset($opts["SERVER"]) ? $opts["SERVER"] : $_SERVER;
        $this->_GET = isset($opts["GET"]) ? $opts["GET"] : $_GET;
        $this->_POST = isset($opts["POST"]) ? $opts["POST"] : $_POST;
        $this->_COOKIE = isset($opts["COOKIE"]) ? $opts["COOKIE"] : $_COOKIE;
        $this->_FILES = isset($opts["FILES"]) ? $opts["FILES"] : $_FILES;
        $this->_templates_path = isset($opts["templates_path"]) 
            ? $opts["templates_path"] : "templates/";
        $this->_plugins_path = isset($opts["plugins_path"]) 
            ? $opts["plugins_path"] : "plugins/";
        $this->_routes = [];
        $this->_plugins = [];
        $this->_template_name = "default";
        $this->_response = [
        "headers" => [],
        "code" => "",
        "reason" => "",
        "body" => "",
        "cookies" => []
        ];
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
        $plugin_path = $this->_plugins_path . $name . "/" . $name . ".php"; 
        if (file_exists($plugin_path)) {
            $className = "\\Machine\\Plugin\\" . $name;
            if (!class_exists($className)) {
                include $plugin_path;
            }
            // instantiate the plugin class passing the Machine object
            $this->_plugins[$name] = new $className($this);
            return $this->_plugins[$name];
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
        return $this->_plugins[$name];
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
     * Set the template name
     *
     * @param string $template_name the template name.
     *
     * @return void
     */
    public function setTemplate($template_name)
    {
        $this->_template_name = $template_name;
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
        return "//" . $this->_SERVER["HTTP_HOST"] . "/" . $this->_templates_path 
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
     * Run the application.
     *
     * @return array A response array
     */
    public function run($silent = false)
    {
        $path = $this->_SERVER["REQUEST_URI"];
        $method = $this->_SERVER["REQUEST_METHOD"];
        $route_matchinfo = $this->_matchRoute($path, $method);    
        
        if ($route_matchinfo) {
            // execute route callback.
            $result = call_user_func_array(
                $route_matchinfo["callback"], 
                $route_matchinfo["params"]
            );
            // the callback may set some response. if not, look for the template.
            if ($this->_response["code"] == "") {
                if (isset($result["data"]) && $result["template"]) {
                    // page found. 200 OK
                    $data = isset($result["data"]) ? $result["data"] : [];
                    $this->_response["code"] = 200;
                    $this->_response["reason"] = "OK";
                    $this->_response["body"] = $this->_getOutputTemplate(
                        $result["template"], 
                        $data
                    );
                } else {
                    // a route was found but nor a response was set or a 
                    //	page was found.
                    $this->_response["code"] = 404;
                    $this->_response["reason"] = "Not found";                    
                }
            } // else a response has been set by the callback function.			
        } else {
            // no route was found matching the request.
            $this->_response["code"] = 404;
            $this->_response["reason"] = "Not found";
        }
        
        if (!$silent) {
            foreach ($this->_response["cookies"] as $cookieparams) {
                call_user_func(setcookie, $cookieparams);
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
        }
        
        return $this->_response;
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
    private function _addRoute($name, $method, $cb)
    {
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
     * @param string $path   the route to check.
     * @param string $method the request method; either "POST" or "GET".
     *
     * @return array|void the matched route infos (callback and params).
     */
    private function _matchRoute($path, $method)
    {
        $foundroutes = [];
        foreach ($this->_routes as $routename => $routearr) {
            // $routename is for example "/route/{parameter}/"
            // here gets transformed to a regexp: "/route/(.*?)/"
            $routename_exp = preg_replace("/\{(.*?)\}/", "(.*?)", $routename);
            // escaping slashes: "\/route\/(.*?)\/"
            $routename_exp = str_replace("/", "\/", $routename_exp);
            // and adding start/end string tags: "/^\/route\/(.*?)\/$/"
            $regexp = "/^" . $routename_exp . "$/";
            
            // time to find if the current route matches
            $matches = [];
            $result = preg_match($regexp, $path, $matches);
            array_shift($matches);
            if ($result == 1 && $this->_checkMatches($matches)) {
                if (isset($this->_routes[$routename][$method])) {
                    $foundroutes[] = [
                    "routename" => $routename,
                    "wildcards" => count($matches), 
                    "callback" => $this->_routes[$routename][$method],
                        "params" => array_merge(
                            // the Machine object is passed as first param
                            [$this], 
                            isset($matches) ? $matches : []
                        )
                    ];
                }
            }
        }

        // Multiple routes may be found. Sort by the less number of wildcards.
        if (count($foundroutes) > 0) {
            usort(
                $foundroutes, function ($a, $b) {
                    return $a["wildcards"] < $b["wildcards"] ? -1 : 1;
                }
            );
            return $foundroutes[0];
        }
    }
  
    /**
     * Check if matches aren't containing the slash / character.
     *
     * @param array $matches an array of parmeter matches.
     *
     * @return boolean
     */    
    private function _checkMatches($matches) 
    {
        foreach ($matches as $m) {
            if (strpos($m, '/') !== false) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Process the template file and mixes it with data.
     *
     * @param string $tpl  the template file name
     * @param array  $data an associative array of data fields.
     *
     * @return string the resulting html output.
     */
    private function _getOutputTemplate($tpl, $data)
    {
        $output = "";
        
		if (file_exists($tpl)) {
			$template_file_name = $tpl;
		} else {
			$template_file_name = $this->_templates_path . $this->_template_name . "/" . $tpl;
        }
		
		if (file_exists($template_file_name)) {
            // plugins are available under their name
            //	this lets to write in templates
            //		$Auth->logged_user_id
            //	instead of
            //		$this->plugin("Auth")->logged_user_id
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
            if (gettype($v) == "string") {
                $tpl = str_replace("{{".$k."}}", $v, $tpl);
            }
        }
        
        // make available some machine functions intended to be used in templates
        // e.g. use {{templatePath}} instead of
        // echo $this->templatePath();
        $tags = [];
        preg_match_all("/{{(.*?)}}/", $tpl, $tags);
        for ($i = 0; $i < count($tags[0]); $i++) {
            $parts = explode("|", $tags[1][$i]);
            $method = array_shift($parts);
            if (method_exists($this, $method)) {
                $value = $this->{$method}($parts);
                $tpl = str_replace($tags[0][$i], $value, $tpl);
            }
        }
        
        // find plugin tags
        //	eg {{<plugin_name>|<param1>|<param2>}}
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
                    //	. $pluginMethod);
                }
            } else {
                //die("Plugin not managed " . $pluginName);
            }
        }
        
        return $tpl;
    }
}
