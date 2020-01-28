<?php
namespace Gt\DomTemplate\Test;

use Gt\DomTemplate\DocumentFragment;
use Gt\DomTemplate\Element;
use Gt\DomTemplate\HTMLDocument;
use Gt\DomTemplate\Test\Helper\Helper;

class HTMLDocumentTest extends TestCase {
	public function testOverriddenClasses() {
		$document = new HTMLDocument("<!doctype html><h1>Test</h1>");
		self::assertInstanceOf(Element::class, $document->firstElementChild);
		$fragment = $document->createDocumentFragment();
		self::assertInstanceOf(DocumentFragment::class, $fragment);
	}

	public function testGetElementById() {
		$document = new HTMLDocument("<!doctype html><h1 id='test'>Test</h1>");
		$element = $document->getElementById("test");
		self::assertInstanceOf(Element::class, $element);
	}

	public function testExtractTemplatesSetsParentInnerHTMLToEmpty() {
		$document = new HTMLDocument(Helper::HTML_TODO_LIST);
		$document->extractTemplates();
		$todoListElement = $document->getElementById("todo-list");
		self::assertSame("", $todoListElement->innerHTML);
	}
}