<?php
namespace Gt\DomTemplate\Test;
use Gt\Dom\HTMLDocument;
use Gt\DomTemplate\BindableCache;
use Gt\DomTemplate\ComponentBinder;
use Gt\DomTemplate\ComponentDoesNotContainContextException;
use Gt\DomTemplate\ComponentExpander;
use Gt\DomTemplate\ElementBinder;
use Gt\DomTemplate\ListBinder;
use Gt\DomTemplate\ListElementCollection;
use Gt\DomTemplate\PlaceholderBinder;
use Gt\DomTemplate\TableBinder;
use Gt\DomTemplate\Test\TestHelper\HTMLPageContent;
use PHPUnit\Framework\TestCase;

class ComponentBinderTest extends TestCase {
	public function testBindKeyValue_invalidContext():void {
		$document = new HTMLDocument(HTMLPageContent::HTML_TODO_CUSTOM_ELEMENT_ALREADY_EXPANDED);
		$componentElement = $document->querySelector("todo-list");
		$sut = new ComponentBinder($document);
		$sut->setComponentBinderDependencies($componentElement);
		self::expectException(ComponentDoesNotContainContextException::class);
		self::expectExceptionMessage("<todo-list> does not contain requested <body>");
		$sut->bindKeyValue("example-key", "example-value", $document->querySelector("body"));
	}

	public function testBindList_invalidContext():void {
		$document = new HTMLDocument(HTMLPageContent::HTML_TODO_CUSTOM_ELEMENT_ALREADY_EXPANDED);
		$componentElement = $document->querySelector("todo-list");
		$sut = new ComponentBinder($document);
		$sut->setComponentBinderDependencies($componentElement);
		self::expectException(ComponentDoesNotContainContextException::class);
		self::expectExceptionMessage("<todo-list> does not contain requested <body>");
		$sut->bindList([], $document->querySelector("body"));
	}

	public function testBindKeyValue_notInContext():void {
		$document = new HTMLDocument(HTMLPageContent::HTML_TODO_CUSTOM_ELEMENT_ALREADY_EXPANDED);
		$componentElement = $document->querySelector("todo-list");
		$sut = new ComponentBinder($document);
		$sut->setDependencies(
			self::createMock(ElementBinder::class),
			self::createMock(PlaceholderBinder::class),
			self::createMock(TableBinder::class),
			self::createMock(ListBinder::class),
			self::createMock(ListElementCollection::class),
			self::createMock(BindableCache::class),
		);
		$sut->setComponentBinderDependencies($componentElement);
		$sut->bindKeyValue("subtitle", "This should not change!");
		self::assertSame("Subtitle here", $document->querySelector("h2")->innerText);
	}

	public function testBindKeyValue():void {
		$document = new HTMLDocument(HTMLPageContent::HTML_TODO_CUSTOM_ELEMENT_ALREADY_EXPANDED);
		$componentElement = $document->querySelector("todo-list");
		$elementBinder = self::createMock(ElementBinder::class);
		$elementBinder->expects(self::once())
			->method("bind")
			->with("listTitle", "This should change!", $componentElement);

		$sut = new ComponentBinder($document);
		$sut->setDependencies(
			$elementBinder,
			self::createMock(PlaceholderBinder::class),
			self::createMock(TableBinder::class),
			self::createMock(ListBinder::class),
			self::createMock(ListElementCollection::class),
			self::createMock(BindableCache::class),
		);
		$sut->setComponentBinderDependencies($componentElement);
		$sut->bindKeyValue("listTitle", "This should change!");
	}
}
