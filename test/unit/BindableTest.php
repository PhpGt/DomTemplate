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

//	public function testBindGetsCorrectChildren() {
//		$document = new HTMLDocument(Helper::HTML_TEMPLATES);
//		$document->extractTemplates();
//		$document->bind([]);
//	}
}