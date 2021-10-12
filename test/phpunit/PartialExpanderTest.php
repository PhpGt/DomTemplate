<?php
namespace Gt\DomTemplate\Test;

use Gt\Dom\Element;
use Gt\Dom\HTMLDocument;
use Gt\DomTemplate\CommentIni;
use Gt\DomTemplate\ModularContent;
use Gt\DomTemplate\ModularContentFileNotFoundException;
use Gt\DomTemplate\PartialExpander;
use Gt\DomTemplate\PartialInjectionPointNotFoundException;
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
		self::expectException(ModularContentFileNotFoundException::class);
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

	public function testExpand_noExtendsSectionOfCommentIni():void {
		$document = DocumentTestFactory::createHTML();
		$modularContent = self::createMock(ModularContent::class);

		$sut = new PartialExpander($document, $modularContent);
		self::assertEmpty($sut->expand());
	}

	public function testExpand_recursive():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_EXTENDS_PARTIAL_VIEW_RECURSIVE);
		$modularContent = self::mockModularContent(
			"_partial", [
				"extended-page" => DocumentTestFactory::HTML_EXTENDS_PARTIAL_VIEW_RECURSIVE_BASE,
				"partial-base" => DocumentTestFactory::HTML_PARTIAL_VIEW,
			]
		);
		$sut = new PartialExpander($document, $modularContent);
		$sut->expand();
		$body = $document->body;
		$main = $body->querySelector("main");
		$outer = $main->querySelector(".outer");
		$inner = $outer->querySelector(".inner");

		self::assertStringContainsString(
			"This is an inner DIV",
			$inner->querySelector("p")->textContent
		);
		self::assertStringContainsString(
			"This is the outer DIV",
			$outer->querySelector("p")->textContent
		);
		self::assertSame("This title was set in the inner partial view.", $document->title);
	}

	public function testExpand_noDataPartialElement():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_EXTENDS_PARTIAL_VIEW);
		$modularContent = self::mockModularContent(
			"_partial", [
// Here, the HTML_COMPONENT isn't expected, because there is no data-partial element.
				"base-page" => DocumentTestFactory::HTML_COMPONENT,
			]
		);
		$sut = new PartialExpander($document, $modularContent);
		self::expectException(PartialInjectionPointNotFoundException::class);
		self::expectExceptionMessage("The current view extends the partial \"base-page\", but there is no element marked with `data-partial`.");
		$sut->expand();
	}
}
