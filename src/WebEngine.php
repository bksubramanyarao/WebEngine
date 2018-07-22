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
class WebEngine {
  private $_slim;
  
  public function __construct($opts=[]) {
    $this->_slim = new \Slim\App;
  }
  
  public function addPage($route, $callback) {  
    $this->_slim->get($route, function ($request, $response, $args) use ($callback) {
      return $response->getBody()->write('<h1>Home page</h1>');
      //return $this->view->render($response, $result["template"], $result["data"]);
    });
  }
  
  public function addAction() {
  }
  
  public function run($silent=false) {
    $response = $this->_slim->run($silent);
    return $response;
  }
}