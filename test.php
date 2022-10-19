<?php
use Gt\Dom\HTMLDocument;
use Gt\DomTemplate\ListBinder;
use Gt\DomTemplate\TemplateCollection;
use Gt\DomTemplate\TemplateElement;

require "vendor/autoload.php";

$html = <<<HTML
<!doctype html>
<ul>

<li data-template>
	<a href="/orders/{{userId}}/{{username??defaultUser}}/">view</a>
</li>

</ul>
HTML;

$kvpList = [
	(object)["userId" => 543, "username" => "win95", "orderCount" => 55],
	(object)["userId" => 559, "username" => "seafoam", "orderCount" => 30],
	(object)["userId" => 274, "username" => "hammatime", "orderCount" => 23],
];

$string = "";
for($iteration = 0; $iteration < 1000; $iteration++) {
	$document = new HTMLDocument($html);
	$orderList = $document->querySelector("ul");

	$templateElement = new TemplateElement($document->querySelector("ul li[data-template]"));
	$templateCollection = new TemplateCollection($document);
	$templateElement->removeOriginalElement();

	$sut = new ListBinder($templateCollection);
	$sut->bindListData($kvpList, $orderList);
	$string .= $document;
	echo memory_get_usage(), PHP_EOL;
}

echo $document;
