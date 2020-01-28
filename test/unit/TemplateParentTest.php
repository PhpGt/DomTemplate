<?php
namespace Gt\DomTemplate\Test;

use Gt\Dom\DocumentFragment;
use Gt\DomTemplate\Element;
use Gt\DomTemplate\HTMLDocument;
use Gt\DomTemplate\Test\Helper\Helper;

class TemplateParentTest extends TestCase {
	const TEST_DIR = "/tmp/phpgt/domtemplate/test";
	const COMPONENT_PATH = "_component";

	public function setUp():void {
		$this->rrmdir(self::TEST_DIR);
		mkdir(
			self::TEST_DIR . "/" . self::COMPONENT_PATH,
			0775,
			true
		);
	}

	public function tearDown():void {
		$this->rrmdir(self::TEST_DIR);
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

	public function testGetTemplateNoName() {
		$document = new HTMLDocument(Helper::HTML_SELECT);
		$document->extractTemplates();
		$select = $document->getElementById("sut");
		self::assertCount(0, $select->children);
		$t = $select->getTemplate();
		self::assertInstanceOf(DocumentFragment::class, $t);
		$t->insertTemplate();
		self::assertCount(1, $select->children);
	}

	public function testExpandComponentsNoComponents() {
		$templateDir = self::TEST_DIR . "/" . self::COMPONENT_PATH;
		$document = new HTMLDocument(Helper::HTML_TEMPLATES);
		$count = $document->expandComponents($templateDir);
		self::assertEquals(0, $count);
	}

	public function testExpandComponents() {
		$componentDir = self::TEST_DIR . "/" . self::COMPONENT_PATH;
		file_put_contents(
			"$componentDir/title-definition-list.html",
			Helper::COMPONENT_TITLE_DEFINITION_LIST
		);
		file_put_contents(
			"$componentDir/title-definition.html",
			Helper::COMPONENT_TITLE_DEFINITION
		);
		file_put_contents(
			"$componentDir/ordered-list.html",
			Helper::COMPONENT_ORDERED_LIST
		);

		$document = new HTMLDocument(
			Helper::HTML_COMPONENTS,
			$componentDir
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

		$count = $document->expandComponents($componentDir);
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

	public function testExistingClassPersistedToExpandedComponent() {
		$componentDirectory = self::TEST_DIR . "/" . self::COMPONENT_PATH;
		file_put_contents(
			"$componentDirectory/ordered-list.html",
			Helper::HTML_COMPONENT_WITH_CLASS_ON_PARENT
		);
		$document = new HTMLDocument(
			Helper::HTML_COMPONENTS,
			$componentDirectory
		);
		$document->expandComponents();

		$expandedComponent = $document->querySelector(
			".c-ordered-list"
		);
		self::assertTrue($expandedComponent->classList->contains(
			"existing-class"
		));
	}

	public function testNestedComponentsExpand() {
		// While the count of the expandCompnents > 0, do it again on the expanded component...
		$templateDir = self::TEST_DIR . "/" . self::COMPONENT_PATH;
		file_put_contents(
			"$templateDir/ordered-list.html",
			Helper::COMPONENT_ORDERED_LIST
		);
		file_put_contents(
			"$templateDir/ordered-list-item.html",
			Helper::COMPONENT_ORDERED_LIST_ITEM
		);
		$document = new HTMLDocument(
			Helper::HTML_COMPONENTS,
			$templateDir
		);
		$document->expandComponents();

		$section = $document->querySelector("section");
		self::assertEquals("ol", $section->lastElementChild->tagName);
		self::assertEquals(
			"li",
			$section->lastElementChild->lastElementChild->tagName
		);
	}

	public function testComponentWithinTemplate() {
		$templateDir = self::TEST_DIR . "/" . self::COMPONENT_PATH;
		file_put_contents(
			"$templateDir/outer-nested-thing.html",
			Helper::COMPONENT_OUTER_NESTED_THING
		);
		file_put_contents(
			"$templateDir/inner-nested-thing.html",
			Helper::COMPONENT_INNER_NESTED_THING
		);
		$document = new HTMLDocument(
			Helper::HTML_TEMPLATE_WITH_NESTED_COMPONENT,
			$templateDir
		);
		$document->expandComponents();
		$document->extractTemplates();

		self::assertCount(
			0,
			$document->querySelectorAll("li")
		);
		self::assertCount(
			0,
			$document->querySelectorAll("nested-thing")
		);

		for($i = 0; $i < 10; $i++) {
			$t = $document->getTemplate("inner-template-item");
			$t->querySelector(".number")->innerText = $i + 1;
			$t->insertTemplate();
		}

		self::assertCount(
			$i,
			$document->querySelectorAll("li")
		);
		self::assertCount(
			0,
			$document->querySelectorAll("nested-thing")
		);
		self::assertCount(
			$i * 2,
			$document->querySelectorAll("p")
		);

		for($i = 0; $i < 10; $i++) {
			$numberElement = $document->querySelectorAll("p .number")[$i];
			self::assertEquals($i + 1, $numberElement->innerText);
		}
	}

	public function testNestedComponentsExpandWhenTemplateInserted() {
		// While the count of the expandCompnents > 0, do it again on the expanded component...
		$templateDir = self::TEST_DIR . "/" . self::COMPONENT_PATH;
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
			Helper::COMPONENT_ORDERED_LIST
		);
		file_put_contents(
			"$templateDir/ordered-list-item.html",
			Helper::COMPONENT_ORDERED_LIST_ITEM
		);
		$document = new HTMLDocument(
			Helper::HTML_COMPONENTS,
			$templateDir
		);
		$document->expandComponents();
		$document->extractTemplates();

		$section = $document->querySelector("section");
		$ol = $section->lastElementChild;
		self::assertEquals("ol", $ol->tagName);
		self::assertEquals("li", $ol->firstElementChild->tagName);

		$expandedComponent = $document->querySelector("dl");
		self::assertInstanceOf(Element::class, $expandedComponent);
		self::assertInstanceOf(Element::class, $expandedComponent->firstElementChild);
		self::assertInstanceOf(Element::class, $expandedComponent->lastElementChild);
		self::assertEquals("dt", $expandedComponent->firstElementChild->tagName);
		self::assertEquals("dd", $expandedComponent->lastElementChild->tagName);
	}

	public function testGetTemplateFromFile() {
		$templateDir = self::TEST_DIR . "/" . self::COMPONENT_PATH;
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

	public function testTemplatePrefixAddedToTemplateElements() {
		$document = new HTMLDocument(Helper::HTML_TEMPLATES);
		$document->extractTemplates();
		$t = $document->getTemplate("list-item");
		$inserted = $t->insertTemplate();
		self::assertTrue($inserted->classList->contains("t-list-item"));
		self::assertFalse($inserted->classList->contains("c-list-item"));
	}

	public function testComponentPrefixAddedToComponentElements() {
		$componentDir = self::TEST_DIR . "/" . self::COMPONENT_PATH;
		file_put_contents(
			"$componentDir/title-definition-list.html",
			Helper::COMPONENT_TITLE_DEFINITION_LIST
		);
		file_put_contents(
			"$componentDir/title-definition.html",
			Helper::COMPONENT_TITLE_DEFINITION
		);
		file_put_contents(
			"$componentDir/ordered-list.html",
			Helper::COMPONENT_ORDERED_LIST
		);

		$document = new HTMLDocument(
			Helper::HTML_COMPONENTS,
			$componentDir
		);
		$document->expandComponents();

		self::assertCount(
			2,
			$document->querySelectorAll(
				".c-title-definition-list,.c-ordered-list"
			)
		);
		self::assertCount(
			0,
			$document->querySelectorAll(
				".t-title-definition-list,.t-ordered-list"
			)
		);
	}

	public function testComponentAndTemplatePrefixAddedToTemplateComponentElement() {
		$componentDir = self::TEST_DIR . "/" . self::COMPONENT_PATH;
		file_put_contents(
			"$componentDir/title-definition-list.html",
			Helper::COMPONENT_TITLE_DEFINITION_LIST
		);
		file_put_contents(
			"$componentDir/title-definition.html",
			Helper::COMPONENT_TITLE_DEFINITION
		);
		file_put_contents(
			"$componentDir/ordered-list.html",
			Helper::COMPONENT_ORDERED_LIST
		);

		$document = new HTMLDocument(
			Helper::HTML_COMPONENTS,
			$componentDir
		);
		$document->expandComponents();

		$t = $document->getTemplate("title-definition-list");
		self::assertInstanceOf(DocumentFragment::class, $t);
		$inserted = $document->body->appendChild($t);

		self::assertTrue(
			$inserted->classList->contains("c-title-definition-list")
		);
		self::assertTrue(
			$inserted->classList->contains("t-title-definition-list")
		);
	}

	public function testComponentAndTemplatePrefixAddedCorrectlyWithNamedTemplate() {
		$componentDir = self::TEST_DIR . "/" . self::COMPONENT_PATH;
		file_put_contents(
			"$componentDir/title-definition-list.html",
			Helper::COMPONENT_TITLE_DEFINITION_LIST
		);
		file_put_contents(
			"$componentDir/title-definition.html",
			Helper::COMPONENT_TITLE_DEFINITION
		);
		file_put_contents(
			"$componentDir/ordered-list.html",
			Helper::COMPONENT_ORDERED_LIST
		);

		$document = new HTMLDocument(
			Helper::HTML_COMPONENTS_WITH_NAMED_TEMPLATE,
			$componentDir
		);
		$document->extractTemplates();
		$document->expandComponents();

		$t = $document->getTemplate("tdlist");
		$inserted = $t->insertTemplate();

		self::assertTrue(
			$inserted->classList->contains("c-title-definition-list")
		);
		self::assertTrue(
			$inserted->classList->contains("t-tdlist")
		);
		self::assertFalse(
			$inserted->classList->contains("t-title-definition-list")
		);
		$classes = explode(" ", $inserted->className);
		self::assertCount(2, $classes);
	}

	public function testExistingClassesAreAddedToComponents() {
		$componentDir = self::TEST_DIR . "/" . self::COMPONENT_PATH;
		file_put_contents(
			"$componentDir/title-definition-list.html",
			Helper::COMPONENT_TITLE_DEFINITION_LIST
		);
		file_put_contents(
			"$componentDir/title-definition.html",
			Helper::COMPONENT_TITLE_DEFINITION
		);

		$document = new HTMLDocument(
			Helper::HTML_COMPONENT_WITH_CLASS,
			$componentDir
		);
		$document->extractTemplates();
		$document->expandComponents();

		$component = $document->querySelector(".c-title-definition-list");
		self::assertTrue(
			$component->classList->contains("source-class")
		);
	}

	/** @link https://github.com/PhpGt/DomTemplate/issues/105 */
	public function testTemplateWithinComponentIsAddedCorrectly() {
		$componentDir = self::TEST_DIR . "/" . self::COMPONENT_PATH;
		file_put_contents(
			"$componentDir/simple-list.html",
			Helper::COMPONENT_WITH_TEMPLATE
		);

		$document = new HTMLDocument(
			Helper::HTML_SIMPLE_LIST_COMPONENT,
			$componentDir
		);
		$document->expandComponents();
		$document->extractTemplates();

		$componentElement = $document->querySelector(".c-simple-list");
		self::assertCount(0, $componentElement->children);

		$componentElement->bindList([
			"one",
			"two",
			"three",
		]);

		self::assertCount(3, $componentElement->children);
	}

	public function testExtractTemplatesSetsParentInnerHTMLToEmpty() {
		$document = new HTMLDocument(Helper::HTML_TODO_LIST);
		$document->extractTemplates();
		$todoListElement = $document->getElementById("todo-list");
		self::assertEmpty($todoListElement->innerHTML);
	}

	public function testExtractTemplatesSetsNestedParentInnerHTMLToEmpty() {
		$document = new HTMLDocument(Helper::HTML_NESTED_LIST);
		$document->extractTemplates();
		$outerListElement = $document->querySelector("ul");
		self::assertEmpty($outerListElement->innerHTML);
	}

	public function testExtractTemplatesSetsNestedParentInnerHTMLPartiallyToEmpty() {
		$document = new HTMLDocument(Helper::HTML_NESTED_LIST);
		$document->extractTemplates();
		$outerListElement = $document->querySelector("ul");
		$document->bindNestedList([
			"Outer 1" => [
				"1:1" => [],
				"1:2" => [],
				"1:3" => [],
			],
			"Outer 2" => [],
			"Outer 3" => [
				"3:1" => [
					"Example" => (object)[
						"name" => "Here I am!"
					]
				],
				"3:2" => [],
			],
		]);

		self::assertNotEmpty($outerListElement->innerHTML);
		self::assertNotEmpty($outerListElement->querySelectorAll("ol")[0]->innerHTML);
		self::assertNotEmpty($outerListElement->querySelectorAll("ol")[2]->innerHTML);
		self::assertEmpty($outerListElement->querySelectorAll("ol")[1]->innerHTML);
	}
}