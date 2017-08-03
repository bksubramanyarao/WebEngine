<?php

namespace Plugin;

class Database {
	private $_machine;
	
	public function __construct($machine)
	{
		$this->_machine = $machine;
		
		// dependency check
		if (!class_exists("\RedBeanPHP\R")) 
		{
			die("Error: \RedBeanPHP\R class not defined. Please run<br><pre>composer require gabordemooij/redbean</pre><br>to add it to your project.");
		}
	}
	
	public function setupSqlite($db_file)
	{
		\RedBeanPHP\R::setup('sqlite:' . $db_file);
	}
	
	public function setupMysql($db_host, $db_user, $db_pass, $db_name) 
	{
		\RedBeanPHP\R::setup('mysql:host=' . $db_host . ';dbname=' . $db_name, $db_user, $db_pass);
	}
}
