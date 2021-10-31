<?php
use Gt\Dom\HTMLDocument;
use Gt\Dom\HTMLElement\HTMLElement;
use Gt\DomTemplate\DocumentBinder;

require __DIR__ . "/../../vendor/autoload.php";

// EXAMPLE CODE: https://github.com/PhpGt/DomTemplate/wiki/Binding#bindlistcallback

$html = <<<HTML
<h1>Shopping list</h1>

<ul>
	<li data-template data-bind:text>Item</li>
</ul>
HTML;

function example(DocumentBinder $binder):void {
	$listData = [
		"Eggs",
		"Potatoes",
		"Butter",
		"Plain flour",
	];
	$binder->bindListCallback($listData, function(HTMLElement $element, $listItem, $listKey) {
		$element->classList->add("item-$listKey");
		return "$listItem (item $listKey)";
	});
}

// END OF EXAMPLE CODE.

$document = new HTMLDocument($html);
$binder = new DocumentBinder($document);
example($binder);
$binder->cleanBindAttributes();
echo $document;
