<?php
require("../vendor/autoload.php");

$opts = [
	"plugins_path" => "../plugins/"
];
$machine = new \Machine\Machine($opts);

$machine->addPlugin("Link");
$machine->addPlugin("Database");
$machine->addPlugin("Form");
$machine->addPlugin("Auth");
$machine->addPlugin("Admin");

$machine->plugin("Database")->setupSqlite("sample.sqlite");

$machine->addPage("/", function($machine) {
	$slides = $machine->Plugin("Database")->find("slide", "active = 1 ORDER BY ord ASC");
	return [
		"template" => "index.php",
		"data" => [
			"slides" => $slides
		]
	];
});

$machine->addPage("/admin/", function($machine) {
	$tables = $machine->Plugin("Database")->getTables();
	return [
		"template" => "admin.php",
		"data" => [
			"tables" => $tables
		]
	];
});

$machine->addAction("/admin/api/{table}/", "GET", function($machine, $table) {
	$db = $machine->plugin("Database");
	$fields = $db->getFields($table);
	$records = $db->findAll($table);
	$result = [
		"fields" => $fields,
		"records" => $records
	];
	echo json_encode($result);
	die();
});

$machine->addAction("/admin/api/{table}/{id}/", "POST", function($machine, $table, $id) {
	$request = $machine->getRequest();

	$db = $machine->plugin("Database");
	$item = $db->getItem($table, $id);	
	
	foreach ($request["POST"] as $field => $value) {
		$item->{$field} = $value;
	}
	$result = $db->update($item);
	
	if (is_null($result)) {
		$machine->sendError("400");
	}
	
	echo json_encode($result);
	die();
});

$machine->addAction("/admin/tools/populate-db/", "GET", function($machine) {
	$db = $machine->plugin("Database");
	$db->addItem("slide", [
		"path" => "images/slide1.jpg",
		"order" => "0"
	]);
	$db->addItem("slide", [
		"path" => "images/slide2.jpg",
		"order" => "1"
	]);
	$db->addItem("slide", [
		"path" => "images/slide3.jpg",
		"order" => "2"
	]);
	$machine->redirect("/admin/");
});

$machine->addAction("/admin/tools/nuke-db/", "GET", function($machine) {
	$machine->plugin("Database")->nuke();
	$machine->redirect("/admin/");
});

$machine->run();	