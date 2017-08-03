<?php

namespace Plugin;

class Link {
	private $_machine;
	
	public function __construct($machine)
	{
		$this->_machine = $machine;
	}
	
	public function Get($params) 
	{
		if (gettype($params) == "string") {
			$params = [$params];
		}
		$slug = $params[0];
		$r = $this->_machine->getRequest();
		return "//" . $r["SERVER"]["HTTP_HOST"] . $slug;
	}
	
	public function Active($params)
	{
		if (gettype($params) == "string") {
			$params = [$params];
		}
		$slug = $params[0];
		$r = $this->_machine->getRequest();
		if ($r["SERVER"]["REQUEST_URI"] == $slug) {
			return "active";
		}
		return "";
	}
}
