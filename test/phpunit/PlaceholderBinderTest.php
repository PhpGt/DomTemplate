<?php
namespace Gt\DomTemplate\Test;

use Gt\Dom\HTMLElement\HTMLAnchorElement;
use Gt\DomTemplate\PlaceholderBinder;
use Gt\DomTemplate\Test\TestFactory\DocumentTestFactory;
use PHPUnit\Framework\TestCase;

class PlaceholderBinderTest extends TestCase {
	public function testBind():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_PLACEHOLDER);
		$greetingElement = $document->querySelector("#test2 .greeting");
		$sut = new PlaceholderBinder();
// We can now bind text to the placeholder, and the text will
// magically be replaced.
		$sut->bind("name", "Cody", $greetingElement);
		self::assertSame("Hello, Cody!", $greetingElement->textContent);
		self::assertSame('<p class="greeting">Hello, Cody!</p>', $greetingElement->outerHTML);
	}

	public function testBind_contextDoesNotLeak():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_PLACEHOLDER);
		$greetingElement = $document->querySelector("#test2 .greeting");
		$sut = new PlaceholderBinder();
		$sut->bind("name", "Cody", $greetingElement);
		self::assertStringContainsString("Cody", $document->querySelector("#test2 .greeting")->textContent);
		self::assertStringNotContainsString("Cody", $document->querySelector("#test1 .greeting")->textContent);
		self::assertStringNotContainsString("Cody", $document->querySelector("#test2a .greeting")->textContent);
	}

	public function testBind_noContextBindsAll():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_PLACEHOLDER);
		$sut = new PlaceholderBinder();
		$sut->bind("name", "Cody", $document);
		self::assertStringContainsString("Cody", $document->querySelector("#test1 .greeting")->textContent);
		self::assertStringContainsString("Cody", $document->querySelector("#test2 .greeting")->textContent);
		self::assertStringContainsString("Cody", $document->querySelector("#test2a .greeting")->textContent);
	}

	public function testBind_attribute():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_PLACEHOLDER);
		$sut = new PlaceholderBinder();
		$testElement = $document->getElementById("test3");
		/** @var HTMLAnchorElement $link */
		$link = $testElement->querySelector("a");
		$sut->bind("repoName", "domtemplate", $testElement);
		self::assertSame("https://www.php.gt/domtemplate", $link->href);
	}

	public function testBind_nullDefault():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_PLACEHOLDER);
		$sut = new PlaceholderBinder();
		$testElement = $document->getElementById("test2");
		$greeting = $testElement->querySelector("p.greeting");
		$sut->bind("name", null, $document);
		self::assertSame("Hello, you!", $greeting->textContent);
	}

	public function testBind_emptyDefault():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_PLACEHOLDER);
		$sut = new PlaceholderBinder();
		$testElement = $document->getElementById("test2");
		$greeting = $testElement->querySelector("p.greeting");
		$sut->bind("name", "", $document);
		self::assertSame("Hello, you!", $greeting->textContent);
	}

	public function testBind_zeroNotDefault():void {
		$document = DocumentTestFactory::createHTML(DocumentTestFactory::HTML_PLACEHOLDER);
		$sut = new PlaceholderBinder();
		$testElement = $document->getElementById("test2");
		$greeting = $testElement->querySelector("p.greeting");
		$sut->bind("name", "0", $document);
		self::assertSame("Hello, 0!", $greeting->textContent);
	}
}
