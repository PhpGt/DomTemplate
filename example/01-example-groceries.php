<?php
require __DIR__ . "/../vendor/autoload.php";

use Gt\DomTemplate\HTMLDocument;

$html = file_get_contents("01-example-groceries.html");
$document = new HTMLDocument($html, "./_component");
$document->extractTemplates();

$data = [
	["id" => 1, "title" => "Tomatoes"],
	["id" => 2, "title" => "Noodles"],
	["id" => 3, "title" => "Cheese"],
	["id" => 4, "title" => "Broccoli"],
];

$outputTo = $document->getElementById("groceries");
$outputTo->bindList($data);

echo $document;