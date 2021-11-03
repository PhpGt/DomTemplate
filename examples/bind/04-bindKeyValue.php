<?php
use Gt\Dom\HTMLDocument;
use Gt\DomTemplate\DocumentBinder;

require __DIR__ . "/../../vendor/autoload.php";

// EXAMPLE CODE: https://github.com/PhpGt/DomTemplate/wiki/Binding#bindkeyvalue

$html = <<<HTML
<h1>Hello, <span data-bind:text="name">you</span>!</h1>
HTML;

function example(DocumentBinder $binder):void {
	$binder->bindKeyValue("name", "Cody");
}

// END OF EXAMPLE CODE.

$document = new HTMLDocument($html);
$binder = new DocumentBinder($document);
example($binder);
$binder->cleanDatasets();
echo $document;
