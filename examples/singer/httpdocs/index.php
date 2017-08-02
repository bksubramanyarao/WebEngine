<?php
require("../../../vendor/autoload.php");

$machine = new \Machine\Machine();
$machine->setTemplate("zSinger");

$machine->addPage("/", function() {
	return [
		"template" => "home.php",
		"data" => []
	];
});

$machine->addPage("/about/", function() {
	return [
		"template" => "single.php",
		"data" => []
	];
});

$machine->addPage("/blog/", function() {
	return [
		"template" => "archive.php",
		"data" => []
	];
});

$machine->addPage("/contacts/", function() {
	return [
		"template" => "contact.php",
		"data" => []
	];
});

$machine->run();	