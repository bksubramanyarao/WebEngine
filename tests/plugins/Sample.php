<?php

namespace Plugin;

class Sample {
	
	private $_machine;
	
	public function __construct($machine)
	{
		$this->_machine = $machine;
	}
	
	public function Plugfun($params)
	{
		if (gettype($params) == "string") {
			$params = [$params];
		}
		return "Sample plugin function called with params "
			. implode(", ", $params);
	}
}