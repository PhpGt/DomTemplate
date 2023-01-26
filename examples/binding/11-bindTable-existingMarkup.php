<?php
use Gt\Dom\HTMLDocument;
use Gt\DomTemplate\DocumentBinder;

require __DIR__ . "/../../vendor/autoload.php";

// EXAMPLE CODE: https://github.com/PhpGt/DomTemplate/wiki/Binding-tables#binding-to-a-table-with-existing-headings

$html = <<<HTML
<!doctype html>
<table>
<thead>
	<tr>
		<th>Delete</th>
		<th data-table-key="id">ID</th>
		<th data-table-key="name">Name</th>
		<th data-table-key="code">Code</th>
		<th>Flag</th>
	</tr>
</thead>
<tbody>
	<tr data-template>
		<td data-bind:class=":deleted">
			<form method="post">
				<input type="hidden" name="id" data-bind:value="@name" />
				<button name="do" value="delete">Delete</button>
			</form>
		</td>
		<td></td>
		<td></td>
		<td></td>
		<td>
			<form method="post">
				<input type="hidden" name="id" data-bind:value="@name" />
				<input name="message" />
				<button name="do" value="flag">Flag</button>
				<button name="do" value="unflag">Flag</button>
			</form>
		</td>
	</tr>
</tbody>
</table>
HTML;

function example(DocumentBinder $binder):void {
	$data = [
		"id" => ["9d3407ac", "0f503f80", "032685b1", "eb08fc32"],
		"code" => ["A44", "B9", "TX420", "TL29"],
		"name" => ["Respiration device N", "Mini filters", "UNG Sponge", "Support Vents"],
		"deleted" => [null, "2022-01-26 17:11:00", null, null],
		"flagged" => [null, null, null, "out of stock"],
	];
	$binder->bindTable($data);
}

// END OF EXAMPLE CODE.

$document = new HTMLDocument($html);
$binder = new DocumentBinder($document);
example($binder);
$binder->cleanDatasets();
echo $document;

/* Output:
<!doctype html>
<html><head></head><body><table>
<thead>
	<tr>
		<th>Delete</th>
		<th>ID</th>
		<th>Name</th>
		<th>Code</th>
		<th>Flag</th>
	</tr>
</thead>
<tbody id="template-parent-63d2b71314f31">
	<tr>
		<td></td>
		<td>9d3407ac</td>
		<td>Respiration device N</td>
		<td></td>
		<td></td>
	</tr>
	...
</tbody>
</table></body></html>
*/
