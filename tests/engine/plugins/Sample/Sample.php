<?php

namespace WebEngine\Plugin;

class Sample {
	
	private $_engine;
	
	// hook
	private $_after_plugfun = [];
	
	public function __construct($engine)
	{
		$this->_engine = $engine;
		$this->_prefixDir = "";
	}
	
	public function addHook($hookname, $func) {
		$hookname = "_" . $hookname;
		if (isset($this->{$hookname})) {
			$this->{$hookname}[] = $func; 
		}
	}
	
	public function Plugfun($params)
	{
		if (gettype($params) == "string") {
			$params = [$params];
		}
		
		// execute hook
		$this->_engine->executeHook($this->_after_plugfun, $params);
		
		return "Sample plugin function called with params "
			. implode(", ", $params);
	}
	
	public function setRoutes($prefixdir)
	{
		$this->_engine->addPage($prefixdir . "/", function() {
			return [
				"template" => __DIR__ . "/template/test.php",
				"data" => [
					"content" => "Home page"
				]
			];
		});
	}
}