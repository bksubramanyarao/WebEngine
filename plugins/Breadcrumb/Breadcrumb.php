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
 * Breadcrumb class
 *
 * A Breadcrumb menu management for the WebEngine
 *
 * @category Plugin
 * @package  WebEngine
 * @author   Paolo Savoldi <paooolino@gmail.com>
 * @license  https://github.com/paooolino/WebEngine/blob/master/LICENSE 
 *           (Apache License 2.0)
 * @link     https://github.com/paooolino/WebEngine
 */
class Breadcrumb
{
    
    private $_engine;
    private $breadcrumb_template = '<span><a href="{{HREF}}">{{LABEL}}</a></span>';
    private $breadcrumb_separator = ' | ';
    
    private $breadcrumbs;
    private $label;
    
    function __construct($engine) 
    {
        $this->_engine = $engine;
        $this->breadcrumbs = [];
        $this->label = "";
    }
    public function add($label, $href) 
    {
        $this->breadcrumbs[] = [
        "label" => $label,
        "href" => $href
        ];
    }
    
    public function setLabel($label) 
    {
        $this->label = $label;
    }
    
    // tags
    
    public function Render($params) 
    {
        $html = [];
        
        foreach($this->breadcrumbs as $breadcrumb) {
            $html[] = $this->_engine->populateTemplate(
                $this->breadcrumb_template, [
                "LABEL" => $breadcrumb["label"],
                "HREF" => $breadcrumb["href"]
                ]
            );
        }
        
        if ($this->label != "") {
            $html[] = $this->label;
        }
        
        return implode($this->breadcrumb_separator, $html);
    }
}
