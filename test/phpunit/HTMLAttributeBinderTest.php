<?php
namespace Gt\DomTemplate\Test;

use Gt\DomTemplate\HTMLAttributeBinder;
use Gt\DomTemplate\Test\TestFactory\DocumentTestFactory;
use PHPUnit\Framework\TestCase;

class HTMLAttributeBinderTest extends TestCase {
	public function testBind_wholeDocument():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_LANGUAGE);
		$sut = new HTMLAttributeBinder();
		$sut->bind("language", "en_GB", $document);
		self::assertSame("en_GB", $document->documentElement->getAttribute("lang"));
	}
}
