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
}
