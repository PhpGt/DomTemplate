<?php
use Gt\Dom\HTMLDocument;
use Gt\DomTemplate\DocumentBinder;

require __DIR__ . "/../../vendor/autoload.php";

// EXAMPLE CODE: https://github.com/PhpGt/DomTemplate/wiki/Binding#bindvalue

$html = <<<HTML
<p data-bind:text>This is a quick example</p>
HTML;

function example(DocumentBinder $binder):void {
	$binder->bindValue("This is an updated example");
}

// END OF EXAMPLE CODE.

$document = new HTMLDocument($html);
$binder = new DocumentBinder($document);
example($binder);
$binder->cleanBindAttributes();
echo $document;
