<?php
namespace Gt\DomTemplate\Test;

use Gt\Dom\HTMLDocument;
use Gt\DomTemplate\CommentIni;
use Gt\DomTemplate\ModularContent;
use Gt\DomTemplate\ModularContentFileNotFoundException;
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
		$document = self::createMock(HTMLDocument::class);
		$modularContent = self::createMock(ModularContent::class);
		$commentIni = self::createMock(CommentIni::class);
		$commentIni->method("get")
			->with("extends")
			->willReturn(null);

		$sut = new PartialExpander($document, $modularContent, $commentIni);
		self::assertEmpty($sut->expand());
	}
}
