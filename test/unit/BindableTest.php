<?php
namespace Gt\DomTemplate\Test;

use Gt\DomTemplate\HTMLDocument;
use Gt\DomTemplate\Test\Helper\Helper;
use PHPUnit\Framework\TestCase;

class BindableTest extends TestCase {
	public function testBindMethodAvailable() {
		$document = new HTMLDocument(Helper::HTML_TEMPLATES);
		$outputTo = $document->querySelector("dl");

		self::assertTrue(
			method_exists($document, "bind"),
			"HTMLDocument is not bindable"
		);
		self::assertTrue(
			method_exists($outputTo, "bind"),
			"Template container element (dl) is not bindable"
		);
	}

	public function testBindMethodOnTemplateElement() {
		$document = new HTMLDocument(Helper::HTML_TEMPLATES);
		$document->extractTemplates();
		$template = $document->getTemplate("title-definition");

		self::assertTrue(
			method_exists($template, "bind"),
			"Template element is not bindable"
		);
	}

	public function testBindExistingElements() {
		$document = new HTMLDocument(Helper::HTML_NO_TEMPLATES);
		$name = "Winston Smith";
		$age = 39;
		$document->bind([
			"name" => $name,
			"age" => $age,
		]);

		$boundDataTestElement = $document->querySelector(".bound-data-test");
		$spanChildren = $boundDataTestElement->querySelectorAll("span");
		self::assertEquals($name,$spanChildren[0]->innerText);
		self::assertEquals($age,$spanChildren[1]->innerText);
	}
}