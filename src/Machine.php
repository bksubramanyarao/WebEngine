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
    private $_POST;
    private $_COOKIE;
  
    private $_routes;
  
    private $_slugify;
  
    private $_templates_path;
  
    /**
     * Create new machine.
     *
     * Available options are:
     * - SERVER
     * - POST
     * - COOKIE
     * - templates_path
     *
     * @param array $opts an array of options.
     */
    public function __construct($opts=[])
    {
        $this->_SERVER = isset($opts["SERVER"]) ? $opts["SERVER"] : $_SERVER;
        $this->_POST = isset($opts["POST"]) ? $opts["POST"] : $_POST;
        $this->_COOKIE = isset($opts["COOKIE"]) ? $opts["COOKIE"] : $_COOKIE;
        $this->_templates_path = isset($opts["templates_path"]) 
            ? $opts["templates_path"] : "templates/";
        $this->_slugify = new \Cocur\Slugify\Slugify();
        $this->_routes = [];
    }
  
    /**
     * Utility function to generate a unique id.
     *
     * @return string a unique id.
     */
    public function uuid()
    {
        return \Ramsey\Uuid\Uuid::uuid4();
    }
      
    /**
     * Add a route without side effects.
     *
     * @param string   $name the route name.
     * @param function $cb   the callback function to be executed.
     *
     * @return void
     */
    public function addPage($name, $cb)
    {
        $this->_addRoute($name, "GET", $cb);
    }
  
    /**
     * Add a route with side effects.
     *
     * @param string   $name   the route name.
     * @param string   $method the method name; either "POST" or "GET".
     * @param function $cb     the callback function to be executed.
     *
     * @return void
     */
    public function addAction($name, $method, $cb)
    {
        $this->_addRoute($name, $method, $cb);
    }
  
    /**
     * Process the template file and mixes it with data.
     *
     * @param string $tpl  the template file name
     * @param array  $data an associative array of data fields.
     *
     * @return string the resulting html output.
     */
    public function _getOutputTemplate($tpl, $data)
    {
		$output = "";
		
		$template_file_name = $this->_templates_path . $tpl;
		if (file_exists($template_file_name)) {
			// data fields are available as regular php variables in templates
			foreach ($data as $k => $v) {
				$$k = $v;
			}
			
			ob_start();
			require($template_file_name);
			$template = ob_get_contents();
			ob_end_clean();
			
			$output = $this->_populateTemplate($template, $data);
		} else {
			$output = "Missing template file: " . $template_file_name;
		}
		
		return $output;
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
    }
  
    /**
     * Run the application.
     *
     * @return array A response array with "output", "ERROR" fields.
     */
    public function run()
    {
		$return_value = [];
		
        $path = $this->_SERVER["REQUEST_URI"];
        $method = $this->_SERVER["REQUEST_METHOD"];
        $route_matchinfo = $this->_matchRoute($path, $method);    
        if ($route_matchinfo) {
			// execute route callback.
			$result = call_user_func_array(
				$route_matchinfo["callback"], 
				$route_matchinfo["params"]
			);
			// if callback redirects, the following instructions will not be
			// executed.
			$data = isset($result["data"]) ? $result["data"] : [];
			$return_value["output"] = $this->_getOutputTemplate($result["template"], $data);
		} else {
			$return_value["ERROR"] = "No route found.";
		}
        
        echo $return_value["output"];
		
		return $return_value;
    }

    /**
     * Add a generic route.
     *
     * @param string   $name   the route name.
     * @param string   $method the method name; either "POST" or "GET".
     * @param function $cb     the callback function to be executed.
     *
     * @return void
     */
    private function _addRoute($name, $method, $cb)
    {
        if (isset($this->_routes[$name][$method])) {
            die(
                "Config Error: duplicated route. Route exists for $method 
			method ($name)"
            );
        }
        if (!isset($this->_routes[$name])) {
            $this->_routes[$name] = [];
        }
        $this->_routes[$name][$method] = $cb;
    }
  
    /**
     * Check if a route matches the passed route.
     *
     * @param string $path   the route to check.
     * @param string $method the request method; either "POST" or "GET".
     *
     * @return array the matched route infos (callback and params).
     * @return void if a route is not found.
     */
    private function _matchRoute($path, $method)
    {
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
            if ($result == 1) {
                if (isset($this->_routes[$routename][$method])) {
                    return [
                    "callback" => $this->_routes[$routename][$method],
                    "params" => array_merge(
                        // the Machine object is passed as first param
                        [$this], 
                        isset($matches[1]) ? $matches[1] : []
                    )
                    ];
                }
            }
        }
    }
  
    /**
     * Mixes a plain html template with data
     *
     * @param string $tpl  the template file name
     * @param array  $data an associative array of data fields.
     *
     * @return string the resulting html output.
     */
    private function _populateTemplate($tpl, $data)
    {
		// populate simple tag with data
		foreach($data as $k => $v) {
			// if a string, try the tag substitution
			if (gettype($v) == "string") {
				$tpl = str_replace("{{".$k."}}", $v, $tpl);
			}
		}
		
		return $tpl;
    }
}
