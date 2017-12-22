<?php
namespace Gt\DomTemplate\Test;

use Gt\Dom\DocumentFragment;
use Gt\DomTemplate\HTMLDocument;
use Gt\DomTemplate\Test\Helper\Helper;
use PHPUnit\Framework\TestCase;

class HTMLDocumentTest extends TestCase {
	public function testTemplateExtractWithNoTemplatesCount() {
		$document = new HTMLDocument(Helper::HTML_NO_TEMPLATES);
		$count = $document->extractTemplates();
		self::assertEquals(0, $count);
	}

	public function testTemplateExtractWithNoTemplatesDoesNotAffectContent() {
		$document = new HTMLDocument(Helper::HTML_NO_TEMPLATES);
		$nodeList = $document->querySelectorAll("*");
		$document->extractTemplates();
		$newNodeList = $document->querySelectorAll("*");
		self::assertCount(count($nodeList), $newNodeList);
	}

	public function testTemplateExtractCount() {
		$document = new HTMLDocument(Helper::HTML_TEMPLATES);
		$count = $document->extractTemplates();
		self::assertEquals(3, $count);
	}

	public function testTemplateExtractRemovesTemplates() {
		$document = new HTMLDocument(Helper::HTML_TEMPLATES);
		$templateElements = $document->querySelectorAll("template,[data-template]");
		self::assertGreaterThan(0, count($templateElements));
		$document->extractTemplates();
		$newTemplateElements = $document->querySelectorAll("template,[data-template]");
		self::assertEquals(0, count($newTemplateElements));
	}

	public function testGetTemplate() {
		$document = new HTMLDocument(Helper::HTML_TEMPLATES);
		$document->extractTemplates();
		$t = $document->getTemplate("title-definition");
		self::assertInstanceOf(DocumentFragment::class, $t);
		self::assertCount(2, $t->children);
	}
}