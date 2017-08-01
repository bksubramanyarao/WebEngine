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
namespace Paooolino;

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
    private $_plugins_path;
    
    public $siteurl;
    
    /**
     * Create new machine.
     *
     * @param array $opts an array of options.
     */
    public function __construct($opts)
    {
        $this->slugify = new \Cocur\Slugify\Slugify();
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
    }
    
    /**
     * Process the template file and mixes it with data.
     *
     * @param string $tpl  the template file name
     * @param array  $data an associative array of data fields.
     *
     * @return string the resulting html output.
     */
    public function getOutputTemplate($tpl, $data)
    {
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
     * @return void
     */
    public function run()
    {
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
    }
    
    /**
     * Check if a route matches the passed route.
     *
     * @param string $path   the route to check.
     * @param string $method the request method; either "POST" or "GET".
     *
     * @return array the matched route infos (callback and params).
     */
    private function _matchRoute($path, $method)
    {
        return [
        "callback" => "",
        "params" => ""
        ]
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
    }
}
