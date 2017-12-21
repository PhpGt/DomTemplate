<?php
namespace Gt\DomTemplate\Test;

use Gt\DomTemplate\HTMLDocument;
use PHPUnit\Framework\TestCase;

class DocumentTest extends TestCase {
	public function testDocument() {
		$document = new HTMLDocument();
		self::assertContains("SPECIAL", $document->documentElement->specialMethod());
	}
}