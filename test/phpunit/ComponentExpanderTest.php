<?php
namespace Gt\DomTemplate\Test;

use Gt\DomTemplate\ComponentExpander;
use Gt\DomTemplate\ModularContent;
use Gt\DomTemplate\ModularContentFileNotFoundException;
use Gt\DomTemplate\Test\TestFactory\DocumentTestFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ComponentExpanderTest extends ModularContentTestCase {
	public function testExpand_doesNothingWhenNoMatchingFiles():void {
		$modularContent = self::mockModularContent("_component");
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_COMPONENT);
		$sut = new ComponentExpander($document, $modularContent);
		self::assertEmpty($sut->expand());
	}

	public function testExpand_returnsArrayOfExpandedElements():void {
		$html = "<h2>This has been replaced!</h2> <p>If you can read this, your custom element is working!</p>";

		$modularContent = self::mockModularContent(
			"_component", [
				"custom-element" => $html
			]
		);
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_COMPONENT);
		$sut = new ComponentExpander($document, $modularContent);
		$expandedElements = $sut->expand();
		self::assertCount(1, $expandedElements);
		self::assertSame("CUSTOM-ELEMENT", $expandedElements[0]->tagName);
		self::assertSame($html, $expandedElements[0]->innerHTML);
	}

	public function testExpand_recursive():void {
		$modularContent = self::mockModularContent(
			"_component", [
				"todo-list" => DocumentTestFactory::HTML_TODO_COMPONENT_TODO_LIST,
				"todo-list-item" => DocumentTestFactory::HTML_TODO_COMPONENT_TODO_LIST_ITEM,
			]
		);
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_TODO_CUSTOM_ELEMENT);
		$sut = new ComponentExpander($document, $modularContent);
		$expandedElements = $sut->expand();
		self::assertCount(2, $expandedElements);
		self::assertSame("TODO-LIST", $expandedElements[0]->tagName);
		self::assertSame("TODO-LIST-ITEM", $expandedElements[1]->tagName);
	}
}
