<?php
namespace Gt\DomTemplate\Test;

use DOMDocument;
use Gt\DomTemplate\Document;
use Gt\DomTemplate\DocumentFragment;
use Gt\DomTemplate\Element;

class DocumentTest extends TestCase {
	public function testOverriddenClasses() {
		$document = new Document();
		self::assertInstanceOf(DOMDocument::class, $document);

		$document->loadHTML("<!doctype html><h1>Test</h1>");
		self::assertInstanceOf(Element::class, $document->firstElementChild);

		$fragment = $document->createDocumentFragment();
		self::assertInstanceOf(DocumentFragment::class, $fragment);
	}
}