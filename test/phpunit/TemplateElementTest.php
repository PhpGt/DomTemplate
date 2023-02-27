<?php
namespace Gt\DomTemplate\Test;

use Gt\Dom\HTMLDocument;
use Gt\DomTemplate\InvalidTemplateElementNameException;
use Gt\DomTemplate\ListElement;
use Gt\DomTemplate\Test\TestHelper\HTMLPageContent;
use PHPUnit\Framework\TestCase;

class TemplateElementTest extends TestCase {
	public function testGetTemplateName_forwardSlashStarter():void {
		$document = new HTMLDocument(HTMLPageContent::HTML_EMPTY);
		$originalElement = $document->createElement("div");
		$originalElement->setAttribute("data-list", "/oh/dear/oh/dear");
		$document->body->appendChild($originalElement);
		$sut = new ListElement($originalElement);
		self::expectException(InvalidTemplateElementNameException::class);
		self::expectExceptionMessage('A template\'s name must not start with a forward slash ("/oh/dear/oh/dear")');
		$sut->getListItemName();
	}

	public function testNextElementSibling():void {
		$document = new HTMLDocument(HTMLPageContent::HTML_TEMPLATE_ELEMENT_WITH_MULTIPLE_DIVS);
		$originalElement = $document->querySelector("[data-list]");
		$originalElementNextElementSibling = $originalElement->nextElementSibling;

		$sut = new ListElement($originalElement);
		$sut->removeOriginalElement();
		self::assertSame($originalElementNextElementSibling, $sut->getListItemNextSibling());
	}

	public function testInsertTemplate():void {
		$document = new HTMLDocument(HTMLPageContent::HTML_TEMPLATE_ELEMENT_WITH_MULTIPLE_DIVS);
		$originalElement = $document->querySelector("[data-list]");
		$originalElementNextElementSibling = $originalElement->nextElementSibling;

		$sut = new ListElement($originalElement);
		$sut->removeOriginalElement();
		$inserted = $sut->insertListItem();
		self::assertSame($originalElementNextElementSibling, $inserted->nextElementSibling);
	}
}
