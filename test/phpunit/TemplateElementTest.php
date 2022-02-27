<?php
namespace Gt\DomTemplate\Test;

use Gt\DomTemplate\InvalidTemplateElementNameException;
use Gt\DomTemplate\TemplateElement;
use Gt\DomTemplate\Test\TestFactory\DocumentTestFactory;
use PHPUnit\Framework\TestCase;

class TemplateElementTest extends TestCase {
	public function testGetTemplateName_forwardSlashStarter():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_EMPTY);
		$originalElement = $document->createElement("div");
		$originalElement->setAttribute("data-template", "/oh/dear/oh/dear");
		$document->body->appendChild($originalElement);
		$sut = new TemplateElement($originalElement);
		self::expectException(InvalidTemplateElementNameException::class);
		self::expectExceptionMessage('A template\'s name must not start with a forward slash ("/oh/dear/oh/dear")');
		$sut->getTemplateName();
	}

	public function testNextElementSibling():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_TEMPLATE_ELEMENT_WITH_MULTIPLE_DIVS);
		$originalElement = $document->querySelector("[data-template]");
		$originalElementNextElementSibling = $originalElement->nextElementSibling;

		$sut = new TemplateElement($originalElement);
		$sut->removeOriginalElement();
		self::assertSame($originalElementNextElementSibling, $sut->getTemplateNextSibling());
	}

	public function testInsertTemplate():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_TEMPLATE_ELEMENT_WITH_MULTIPLE_DIVS);
		$originalElement = $document->querySelector("[data-template]");
		$originalElementNextElementSibling = $originalElement->nextElementSibling;

		$sut = new TemplateElement($originalElement);
		$sut->removeOriginalElement();
		$inserted = $sut->insertTemplate();
		self::assertSame($originalElementNextElementSibling, $inserted->nextElementSibling);
	}
}
