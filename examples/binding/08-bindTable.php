<?php
use Gt\Dom\HTMLDocument;
use Gt\DomTemplate\DocumentBinder;

require __DIR__ . "/../../vendor/autoload.php";

// EXAMPLE CODE: https://github.com/PhpGt/DomTemplate/wiki/Binding#bindtable

$html = <<<HTML
<table>
	<thead>
		<tr>
			<th>Day</th>
			<th>Weather</th>
		</tr>
	</thead>
</table>
HTML;

function example(DocumentBinder $binder):void {
	$tableData = [
		"Day" => ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
		"Weather" => ["Rain", "Cloud", "Cloud", "Sun", "Sun", "Cloud", "Cloud"],
	];

	$binder->bindTable($tableData);
}

// END OF EXAMPLE CODE.

$document = new HTMLDocument($html);
$binder = new DocumentBinder($document);
example($binder);
$binder->cleanBindAttributes();
echo $document;
