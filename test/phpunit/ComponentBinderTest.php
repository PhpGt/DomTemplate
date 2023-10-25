<?php
namespace Gt\DomTemplate\Test;
use Gt\Dom\HTMLDocument;
use Gt\DomTemplate\ComponentBinder;
use Gt\DomTemplate\ComponentDoesNotContainContextException;
use Gt\DomTemplate\ComponentExpander;
use Gt\DomTemplate\Test\TestHelper\HTMLPageContent;
use PHPUnit\Framework\TestCase;

class ComponentBinderTest extends TestCase {
	public function testBindKeyValue_invalidContext():void {
		$document = new HTMLDocument(HTMLPageContent::HTML_TODO_CUSTOM_ELEMENT_ALREADY_EXPANDED);
		$componentElement = $document->querySelector("todo-list");
		$sut = new ComponentBinder($componentElement, $document);
		self::expectException(ComponentDoesNotContainContextException::class);
		self::expectExceptionMessage("<todo-list> does not contain requested <body>");
		$sut->bindKeyValue("example-key", "example-value", $document->querySelector("body"));
	}

	public function testBindList_invalidContext():void {
		$document = new HTMLDocument(HTMLPageContent::HTML_TODO_CUSTOM_ELEMENT_ALREADY_EXPANDED);
		$componentElement = $document->querySelector("todo-list");
		$sut = new ComponentBinder($componentElement, $document);
		self::expectException(ComponentDoesNotContainContextException::class);
		self::expectExceptionMessage("<todo-list> does not contain requested <body>");
		$sut->bindList([], $document->querySelector("body"));
	}

	public function testBindKeyValue_notInContext():void {
		$document = new HTMLDocument(HTMLPageContent::HTML_TODO_CUSTOM_ELEMENT_ALREADY_EXPANDED);
		$componentElement = $document->querySelector("todo-list");
		$sut = new ComponentBinder($componentElement, $document);

		$sut->bindKeyValue("subtitle", "This should not change!");
		self::assertSame("Subtitle here", $document->querySelector("h2")->innerText);
	}

	public function testBindKeyValue():void {
		$document = new HTMLDocument(HTMLPageContent::HTML_TODO_CUSTOM_ELEMENT_ALREADY_EXPANDED);
		$componentElement = $document->querySelector("todo-list");
		$sut = new ComponentBinder($componentElement, $document);

		$sut->bindKeyValue("listTitle", "This should change!");
		self::assertSame("This should change!", $document->querySelector("h3")->innerText);
	}
}
