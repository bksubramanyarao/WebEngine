<?php
/**
 * WebEngine
 *
 * PHP version 5
 *
 * @category  Plugin
 * @package   WebEngine
 * @author    Paolo Savoldi <paooolino@gmail.com>
 * @copyright 2017 Paolo Savoldi
 * @license   https://github.com/paooolino/WebEngine/blob/master/LICENSE 
 *            (Apache License 2.0)
 * @link      https://github.com/paooolino/WebEngine
 */
namespace WebEngine\Plugin;

/**
 * Link class
 *
 * A class grouping useful methods to manage links.
 *
 * @category Plugin
 * @package  WebEngine
 * @author   Paolo Savoldi <paooolino@gmail.com>
 * @license  https://github.com/paooolino/WebEngine/blob/master/LICENSE 
 *           (Apache License 2.0)
 * @link     https://github.com/paooolino/WebEngine
 */
class Link
{
  private $_engine;
  private $_routes;

  /**
   * Link plugin constructor.
   *
   * The user should not use it directly, as this is called by the WebEngine.
   *
   * @param WebEngine $engine the WebEngine instance.
   */
  public function __construct($engine)
  {
    $this->_engine = $engine;
    $this->_routes = [];
  }
    
  /**
   * Given a name or a slug, gives the complete link.
   *
   * @param array $params
   *
   * @return string The complete link.
   */
  public function Get($params) 
  {
    if (gettype($params) == "string") {
        $params = [$params];
    }
    // the first get param may be a route or a route name
    $name = $params[0];
    $route = isset($this->_routes[$name]) ? $this->_routes[$name] : $name;
  
    // find and fill route parameters with get parameters
    if (count($params) > 1) {
      $matches = [];
      $regexp = "/\{(.*?)\}/";
      $result = preg_match_all($regexp, $route, $matches);
      for ($i = 0; $i < count($matches[0]); $i++) {
        if (isset($params[$i+1])) {
          $route = str_replace($matches[0][$i], $params[$i+1], $route);
        } else {
          break;
        }
      }
    }
    
    $r = $this->_engine->getRequest();
    return "//" . $r["SERVER"]["HTTP_HOST"] . $this->_engine->basepath . $route;
  }
    
  /**
   * Given a slug, return a string indicating if it matches the current URL.
   *
   * @param array $params
   *
   * @return string "active" if the request matches the slug. Empty string 
   *                  otherwise
   */
  public function Active($params)
  {
    if (gettype($params) == "string") {
      $params = [$params];
    }
    $slug = $params[0];
    $r = $this->_engine->getRequest();
    if ($r["SERVER"]["REQUEST_URI"] == $slug) {
      return "active";
    }
    return "";
  }
	
  /**
   * Set a name for a route.
   *
   * @param string $name A name for the route.
   * @param string $route The route to map.
   *
   * @return void
   */
  public function setRoute($name, $route)
  {
    $this->_routes[$name] = $route;
  }
	
  /**
   * Return the route mapped to a name.
   *
   * @param array $params
   *
   * @return string The route name.
   */
  public function GetRoute($params)
  {
    if (gettype($params) == "string") {
      $params = [$params];
    }
    $name = $params[0];
    return isset($this->_routes[$name]) ? $this->_routes[$name] : $name;
  }
  
  /**
   *  Return a route name, given the route path.
   *
   *  Useful for debug purposes.
   */
  public function getRouteName($route)
  {
    foreach ($this->_routes as $k => $v) {
      if ($v == $route) {
        return $k;
      }
    }
    return "";
  }
}