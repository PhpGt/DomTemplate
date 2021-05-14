<?php
namespace Gt\DomTemplate\Test;

use Gt\DomTemplate\PlaceholderBinder;
use Gt\DomTemplate\Test\TestFactory\DocumentTestFactory;
use PHPUnit\Framework\TestCase;

class PlaceholderBinderTest extends TestCase {
	public function testConstructor_noBind():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_PLACEHOLDER);
		$greetingElement = $document->querySelector("#test1 .greeting");

// Before a TemplateCollection is introduced, the placeholders are visible
// in the source.
		self::assertSame("Hello, {{name}}!", $greetingElement->textContent);
		new PlaceholderBinder($document);
// Now that a TempalteCollection exists, the default text is shown, which
// in this case is just the bind key, because no other default is supplied.
		self::assertSame("Hello, name!", $greetingElement->textContent);
	}

	public function testConstructor_noBind_default():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_PLACEHOLDER);
		$greetingElement = $document->querySelector("#test2 .greeting");

		self::assertSame("Hello, {{name ?? you}}!", $greetingElement->textContent);
		new PlaceholderBinder($document);
		self::assertSame("Hello, you!", $greetingElement->textContent);
	}

	public function testConstructor_noBind_defaultDifferentSyntax():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_PLACEHOLDER);
		$greetingElement = $document->querySelector("#test2a .greeting");

		self::assertSame("Hello, {{name??you}}!", $greetingElement->textContent);
		new PlaceholderBinder($document);
		self::assertSame("Hello, you!", $greetingElement->textContent);
	}

	public function testBind():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_PLACEHOLDER);
		$greetingElement = $document->querySelector("#test2 .greeting");
		$sut = new PlaceholderBinder($document);
// We can now bind text to the placeholder, and the text will
// magically be replaced.
		$sut->bind("name", "Cody", $greetingElement);
		self::assertSame("Hello, Cody!", $greetingElement->textContent);
		self::assertSame('<p class="greeting">Hello, Cody!</p>', $greetingElement->outerHTML);
	}
}
