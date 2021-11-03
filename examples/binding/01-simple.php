<?php
use Gt\Dom\HTMLDocument;
use Gt\DomTemplate\DocumentBinder;

require __DIR__ . "/../../vendor/autoload.php";

// EXAMPLE CODE: https://github.com/PhpGt/DomTemplate/wiki/Binding#simplest-possible-usage

$html = <<<HTML
<h1 data-bind:text>Name</h1>
HTML;

function example(DocumentBinder $binder):void {
	$binder->bindValue("Cody");
}

// END OF EXAMPLE CODE.

$document = new HTMLDocument($html);
$binder = new DocumentBinder($document);
example($binder);
$binder->cleanDatasets();
echo $document;
