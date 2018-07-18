<?php
namespace WebEngine\Controllers;

function testController() {
  return [
    "template" => "test.php",
    "data" => [
      "content" => "External controller!"
    ]
  ];
}