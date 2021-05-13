<?php
namespace Gt\DomTemplate\Test;

use Gt\DomTemplate\ListBinder;
use Gt\DomTemplate\TableElementNotFoundInContextException;
use Gt\DomTemplate\TemplateCollection;
use Gt\DomTemplate\TemplateElement;
use Gt\DomTemplate\Test\TestFactory\DocumentTestFactory;
use PHPUnit\Framework\TestCase;

class ListBinderTest extends TestCase {
	public function testBindList_emptyList():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_LIST_TEMPLATE);

		$templateCollection = self::createMock(TemplateCollection::class);
// The template collection should never even be touched if the list is empty.
		$templateCollection->expects(self::never())
			->method("get");

		$sut = new ListBinder();
		$boundCount = $sut->bindListData(
			[],
			$document,
			$templateCollection
		);
		self::assertSame(0, $boundCount);
	}

	public function testBindList_noMatchingTemplate():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_LIST_TEMPLATE);
		$templateCollection = self::createMock(TemplateCollection::class);
		$templateCollection->expects(self::once())
			->method("get")
			->with($document->documentElement, "missing")
			->willReturnCallback(function() {
				throw new TableElementNotFoundInContextException();
			});

		$sut = new ListBinder();
		self::expectException(TableElementNotFoundInContextException::class);
		$sut->bindListData(
			["one", "two", "three"],
			$document,
			$templateCollection,
			"missing"
		);
	}

	public function testBindList_simpleList():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_LIST_TEMPLATE);
		$templateElement = new TemplateElement($document->querySelector("li[data-template]"));

		$templateCollection = self::createMock(TemplateCollection::class);
		$templateCollection->expects(self::once())
			->method("get")
			->with($document->documentElement, null)
			->willReturn($templateElement);

		$ul = $document->querySelector("ul");
		self::assertCount(
			0,
			$ul->children,
			"There should be no LI elements in the UL at the start of the test"
		);

		$testData = ["one", "two", "three"];
		$sut = new ListBinder();
		$boundCount = $sut->bindListData(
			$testData,
			$document,
			$templateCollection
		);

		self::assertSame(count($testData), $boundCount);
		self::assertCount(
			$boundCount,
			$ul->children,
			"The correct number of LI elements should have been inserted into the UL"
		);
	}
}
