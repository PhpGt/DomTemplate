<?php
namespace Gt\DomTemplate\Test;

use Gt\Dom\DocumentFragment;
use Gt\DomTemplate\Element;
use Gt\DomTemplate\HTMLDocument;
use Gt\DomTemplate\Test\Helper\Helper;
use PHPUnit\Framework\TestCase;

class TemplateParentTest extends TestCase {
	const TEST_DIR = "/tmp/phpgt/domtemplate/test";
	const TEMPLATE_PATH = "_template";

	public function setUp() {
		exec("rm -rf " . self::TEST_DIR);
		mkdir(
			self::TEST_DIR . "/" . self::TEMPLATE_PATH,
			0775,
			true
		);
	}

	public function tearDown() {
		exec("rm -rf " . self::TEST_DIR);
	}

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

	public function testExpandComponentsNoComponents() {
		$templateDir = self::TEST_DIR . "/" . self::TEMPLATE_PATH;
		$document = new HTMLDocument(Helper::HTML_TEMPLATES);
		$count = $document->expandComponents($templateDir);
		self::assertEquals(0, $count);
	}

	public function testExpandComponents() {
		$templateDir = self::TEST_DIR . "/" . self::TEMPLATE_PATH;
		file_put_contents(
			"$templateDir/title-definition-list.html",
			Helper::COMPONENT_TITLE_DEFINITION_LIST
		);
		file_put_contents(
			"$templateDir/title-definition.html",
			Helper::COMPONENT_TITLE_DEFINITION
		);
		file_put_contents(
			"$templateDir/ordered-list.html",
			Helper::COMPONENT_TITLE_ORDERED_LIST
		);

		$document = new HTMLDocument(
			Helper::HTML_COMPONENTS,
			$templateDir
		);
		self::assertInstanceOf(
			Element::class,
			$document->querySelector("title-definition-list")
		);
		self::assertInstanceOf(
			Element::class,
			$document->querySelector("ordered-list")
		);

		$elementBeforeOrderedList = $document->querySelector("ordered-list")->previousElementSibling;

		$count = $document->expandComponents($templateDir);
		self::assertEquals(2, $count);
		self::assertNull(
			$document->querySelector("title-definition-list")
		);
		self::assertNull(
			$document->querySelector("ordered-list")
		);

		self::assertEquals(
			"ol",
			$elementBeforeOrderedList->nextElementSibling->tagName
		);
	}

	public function testNestedComponentsExpand() {
		// While the count of the expandCompnents > 0, do it again on the expanded component...
		$templateDir = self::TEST_DIR . "/" . self::TEMPLATE_PATH;
		file_put_contents(
			"$templateDir/title-definition-list.html",
			Helper::COMPONENT_TITLE_DEFINITION_LIST
		);
		file_put_contents(
			"$templateDir/title-definition.html",
			Helper::COMPONENT_TITLE_DEFINITION
		);
		$document = new HTMLDocument(
			Helper::HTML_COMPONENTS,
			$templateDir
		);
		$document->expandComponents();

		$expandedComponent = $document->querySelector("dl");
		self::assertInstanceOf(Element::class, $expandedComponent);
		self::assertInstanceOf(Element::class, $expandedComponent->firstElementChild);
		self::assertInstanceOf(Element::class, $expandedComponent->lastElementChild);
		self::assertEquals("dt", $expandedComponent->firstElementChild->tagName);
		self::assertEquals("dd", $expandedComponent->lastElementChild->tagName);
	}

	public function testGetTemplateFromFile() {
		$templateDir = self::TEST_DIR . "/" . self::TEMPLATE_PATH;
		file_put_contents(
			"$templateDir/title-definition.html",
			Helper::COMPONENT_TITLE_DEFINITION
		);
		$document = new HTMLDocument(
			Helper::HTML_NO_TEMPLATES,
			$templateDir
		);

		$fragment = $document->getTemplate("title-definition");
		self::assertInstanceOf(DocumentFragment::class, $fragment);
	}

	public function testTemplateAttributeTidied() {
		$document = new HTMLDocument(Helper::HTML_TEMPLATES);
		$document->extractTemplates();
		$t = $document->getTemplate("list-item");
		$inserted = $t->insertTemplate();
		self::assertNull($inserted->getAttribute("data-template"));
	}
}