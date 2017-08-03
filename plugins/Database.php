<?php

namespace Plugin;

// dependency check
if (!class_exists("\RedBeanPHP\R")) 
{
	die("Error: \RedBeanPHP\R class not defined. Please run<br><pre>composer require gabordemooij/redbean</pre><br>to add it to your project.");
}
		
use \RedBeanPHP\R;

class Database {
	private $_machine;
	
	public function __construct($machine)
	{
		$this->_machine = $machine;
	}
	
	public function setupSqlite($db_file)
	{
		R::setup('sqlite:' . $db_file);
	}
	
	public function setupMysql($db_host, $db_user, $db_pass, $db_name) 
	{
		R::setup('mysql:host=' . $db_host . ';dbname=' . $db_name, $db_user, $db_pass);
	}
	
	public function find($collectionName, $sqlCondition, $boundData = []) 
	{
		return R::find($collectionName, " " . $sqlCondition . " ", $boundData);
	}
	
	public function findAll($collectionName) 
	{
		return R::findAll($collectionName);
	}
	
	public function getTables()
	{
		return R::inspect();
	}
	
	public function getFields($tablename)
	{
		return R::inspect($tablename);
	}
	
	public function addItem($collectionName, $data) 
	{
		$item = R::dispense($collectionName);
		foreach ($data as $k => $v) {
			$item->{$k} = $v;
		}
		$id = R::store($item);
		return $item;
	}
	
	public function nuke() 
	{
		R::nuke();
	}
}
