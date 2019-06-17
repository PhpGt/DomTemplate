<?php
namespace Gt\DomTemplate\Test;

use Gt\DomTemplate\DocumentFragment;
use Gt\DomTemplate\Element;
use Gt\DomTemplate\HTMLDocument;

class HTMLDocumentTest extends TestCase {
	public function testOverriddenClasses() {
		$document = new HTMLDocument("<!doctype html><h1>Test</h1>");
		self::assertInstanceOf(Element::class, $document->firstElementChild);
		$fragment = $document->createDocumentFragment();
		self::assertInstanceOf(DocumentFragment::class, $fragment);
	}


}