<?php
namespace Gt\DomTemplate\Test;

use Gt\DomTemplate\ModularContentExpander;
use Gt\DomTemplate\PartialExpander;
use Gt\DomTemplate\Test\TestFactory\DocumentTestFactory;

class PartialExpanderTest extends ModularContentTestCase {
	public function testExpand_noMatchingPartial():void {
		$modularContent = self::mockModularContent(
			"_partial", [
				"nothing" => "this doesn't exist",
			]
		);
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_EXTENDS_PARTIAL_VIEW);
		$sut = new PartialExpander(
			$document,
			$modularContent
		);
		self::assertEmpty($sut->expand());
	}

	public function testExpand():void {
		$modularContent = self::mockModularContent(
			"_partial", [
				"base-page" => DocumentTestFactory::HTML_PARTIAL_VIEW
			]
		);
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_EXTENDS_PARTIAL_VIEW);

		$mainElement = $document->querySelector("body>main");
		self::assertNull($mainElement);

		$sut = new PartialExpander(
			$document,
			$modularContent
		);
		$expandedPartials = $sut->expand();
		$mainElement = $document->querySelector("body>main");
		self::assertNotNull($mainElement);

		self::assertCount(1, $expandedPartials);
		self::assertSame("base-page", $expandedPartials[0]);

		self::assertSame("My website!", $document->querySelector("body>header>h1")->textContent);
		self::assertStringContainsString(
			"The page content will go in here.",
			$mainElement->firstChild->textContent,
			"The original content of the partial element should not be removed."
		);

		self::assertSame("Hello from within a sub-template!", $mainElement->querySelector("h1")->textContent);
	}
}
