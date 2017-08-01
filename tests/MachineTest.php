<?php

namespace Machine\Tests;

require './vendor/autoload.php';

class MachineTest extends \PHPUnit_Framework_TestCase
{
	private function _request($method, $path)
	{
		return [
			"SERVER" => [
				"REQUEST_METHOD" => $method,
				"REQUEST_URI" => $path
			]
		];
	}
	
	public function testMachineConstructor()
	{
		$opts = $this->_request("GET", "/");
		$machine = new \Machine\Machine($opts);
	}
}
