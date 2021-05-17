<?php
namespace Gt\DomTemplate\Test;

use Gt\Dom\HTMLElement\HTMLAnchorElement;
use Gt\DomTemplate\PlaceholderCollection;
use Gt\DomTemplate\Test\TestFactory\DocumentTestFactory;
use PHPUnit\Framework\TestCase;

class PlaceholderCollectionTest extends TestCase {
	public function testConstructor_noBind():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_PLACEHOLDER);
		$greetingElement = $document->querySelector("#test1 .greeting");

// Before a TemplateCollection is introduced, the placeholders are visible
// in the source.
		self::assertSame("Hello, {{name}}!", $greetingElement->textContent);
		$sut = new PlaceholderCollection();
// Now that a PlaceholderCollection exists, the default text is shown, which
// in this case is just the bind key, because no other default is supplied.
		$sut->extract($document);
		self::assertSame("Hello, name!", $greetingElement->textContent);
	}

	public function testConstructor_noBind_default():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_PLACEHOLDER);
		$greetingElement = $document->querySelector("#test2 .greeting");

		self::assertSame("Hello, {{name ?? you}}!", $greetingElement->textContent);
		$sut = new PlaceholderCollection();
		$sut->extract($document);
		self::assertSame("Hello, you!", $greetingElement->textContent);
	}

	public function testConstructor_noBind_defaultDifferentSyntax():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_PLACEHOLDER);
		$greetingElement = $document->querySelector("#test2a .greeting");

		self::assertSame("Hello, {{name??you}}!", $greetingElement->textContent);
		$sut = new PlaceholderCollection();
		$sut->extract($document);
		self::assertSame("Hello, you!", $greetingElement->textContent);
	}

	public function testBind_attributeDefault():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_PLACEHOLDER);
		$testElement = $document->getElementById("test4");
		/** @var HTMLAnchorElement $link */
		$link = $testElement->querySelector("a");
		self::assertSame(
			"https://www.php.gt/{{repoName ?? domtemplate}}",
			$link->href
		);
		$sut = new PlaceholderCollection();
		$sut->extract($document);
		self::assertSame(
			"https://www.php.gt/domtemplate",
			$link->href
		);
	}
}
