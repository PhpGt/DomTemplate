<?php
namespace Gt\DomTemplate\Test;

use Gt\DomTemplate\TemplateCollection;
use Gt\DomTemplate\Test\TestFactory\DocumentTestFactory;
use PHPUnit\Framework\TestCase;

class TemplateCollectionTest extends TestCase {
	public function testGet_noName():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_LIST_TEMPLATE);
		$sut = new TemplateCollection($document);
		$templateElement = $sut->get($document);
		$inserted = $templateElement->insertTemplate();
		self::assertSame("LI", $inserted->tagName);
		$ul = $document->querySelector("ul");
		self::assertSame($ul, $templateElement->getTemplateParent());
		self::assertSame($ul, $inserted->parentElement);
	}
}
