<?php
use Gt\Dom\HTMLDocument;
use Gt\DomTemplate\DocumentBinder;

require __DIR__ . "/../../vendor/autoload.php";

// EXAMPLE CODE: https://github.com/PhpGt/DomTemplate/wiki/Binding#bindlist

$html = <<<HTML
<h1>Shopping list</h1>

<ul>
	<li data-template data-bind:text>Item name</li>
</ul>
HTML;

function example(DocumentBinder $binder):void {
	$listData = [
		"Eggs",
		"Potatoes",
		"Butter",
		"Plain flour",
	];
	$binder->bindList($listData);
}

// END OF EXAMPLE CODE.

$document = new HTMLDocument($html);
$binder = new DocumentBinder($document);
example($binder);
$binder->cleanDatasets();
echo $document;
