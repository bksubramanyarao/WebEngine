<?php
require("../../../vendor/autoload.php");

$opts = [
	"plugins_path" => "../../../plugins/"
];
$machine = new \Machine\Machine($opts);
$machine->setTemplate("zSinger");

$machine->addPlugin("Link");
$machine->addPlugin("Form");

$machine->addPage("/", function() {
	return [
		"template" => "home.php",
		"data" => [
			"name" => "Travis",
			"surname" => "Johnson",
			"job" => "IT Support Manager<br>& Programmer Analyst"
		]
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

$machine->addPage("/admin/", function($machine) {
	return [
		"template" => "admin.php",
		"data" => []
	];
});

$machine->run();	