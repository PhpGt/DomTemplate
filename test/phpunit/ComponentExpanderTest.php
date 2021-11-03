<?php
namespace Gt\DomTemplate\Test;

use Gt\DomTemplate\ComponentExpander;
use Gt\DomTemplate\PartialContent;
use Gt\DomTemplate\PartialContentFileNotFoundException;
use Gt\DomTemplate\Test\TestFactory\DocumentTestFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ComponentExpanderTest extends PartialContentTestCase {
	public function testExpand_doesNothingWhenNoMatchingFiles():void {
		$partialContent = self::mockPartialContent("_component");
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_COMPONENT);
		$sut = new ComponentExpander($document, $partialContent);
		self::assertEmpty($sut->expand());
	}

	public function testExpand_returnsArrayOfExpandedElements():void {
		$html = "<h2>This has been replaced!</h2> <p>If you can read this, your custom element is working!</p>";

		$partialContent = self::mockPartialContent(
			"_component", [
				"custom-element" => $html
			]
		);
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_COMPONENT);
		$sut = new ComponentExpander($document, $partialContent);
		$expandedElements = $sut->expand();
		self::assertCount(1, $expandedElements);
		self::assertSame("CUSTOM-ELEMENT", $expandedElements[0]->tagName);
		self::assertSame($html, $expandedElements[0]->innerHTML);
	}

	public function testExpand_recursive():void {
		$partialContent = self::mockPartialContent(
			"_component", [
				"todo-list" => DocumentTestFactory::HTML_TODO_COMPONENT_TODO_LIST,
				"todo-list-item" => DocumentTestFactory::HTML_TODO_COMPONENT_TODO_LIST_ITEM,
			]
		);
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_TODO_CUSTOM_ELEMENT);
		$sut = new ComponentExpander($document, $partialContent);
		$expandedElements = $sut->expand();
		self::assertCount(2, $expandedElements);
		self::assertSame("TODO-LIST", $expandedElements[0]->tagName);
		self::assertSame("TODO-LIST-ITEM", $expandedElements[1]->tagName);
	}

	public function testExpand_empty():void {
		$partialContent = self::mockPartialContent(
			"_component", [
				"empty-component" => "",
			]
		);
		$document = DocumentTestFactory::createHTML("<!doctype html><html><body><empty-component /></body></html>");
		$sut = new ComponentExpander($document, $partialContent);
		$expandedElements = $sut->expand();
		self::assertCount(1, $expandedElements);
		self::assertEquals("<empty-component></empty-component>", $document->body->innerHTML);
	}
}
